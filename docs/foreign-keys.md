# Foreign Keys: Native vs Logical

## The Core Constraint

Relational database engines enforce foreign keys **within a single connection**. A MariaDB
server cannot verify that an `animalID` column in the `booking` database refers to a valid
row in a MySQL table on a different machine. Therefore this application uses two categories
of referential integrity:

- **Native FK** — a `FOREIGN KEY` constraint enforced by the DB engine. Only possible when
  both the referencing column and the referenced table are on the **same connection**.
- **Logical FK** — an `unsignedBigInteger` column that *semantically* references a row in
  another database. No DB-level constraint exists. Integrity is enforced at the application
  layer (see `ForeignKeyValidator`, triggers, and model validation).

---

## Native Foreign Keys (same-connection, DB-enforced)

### `reporting` connection (MariaDB)

| Table | Column | References | On Delete |
|---|---|---|---|
| `rescue` | `reportID` | `report.id` | CASCADE |
| `image` | `reportID` | `report.id` | CASCADE |

### `shelter` connection (MySQL)

| Table | Column | References | On Delete |
|---|---|---|---|
| `slot` | `sectionID` | `section.id` | SET NULL |
| `inventory` | `slotID` | `slot.id` | SET NULL |
| `inventory` | `categoryID` | `category.id` | SET NULL |

### `animals` connection (MySQL)

| Table | Column | References | On Delete |
|---|---|---|---|
| `vet` | `clinicID` | `clinic.id` | SET NULL |
| `vaccination` | `animalID` | `animal.id` | SET NULL |
| `vaccination` | `vetID` | `vet.id` | SET NULL |
| `medical` | `animalID` | `animal.id` | CASCADE |
| `medical` | `vetID` | `vet.id` | SET NULL |
| `animal_profile` | `animalID` | `animal.id` | CASCADE |

### `booking` connection (MariaDB)

| Table | Column | References | On Delete |
|---|---|---|---|
| `adoption` | `bookingID` | `booking.id` | CASCADE |
| `adoption` | `transactionID` | `transaction.id` | SET NULL |
| `animal_booking` | `bookingID` | `booking.id` | CASCADE |
| `visit_list_animal` | `listID` | `visit_list.id` | CASCADE |

### `users` connection (PostgreSQL)

| Table | Column | References | On Delete |
|---|---|---|---|
| `adopter_profile` | `adopterID` | `users.id` | CASCADE |

---

## Logical Foreign Keys (cross-DB, application-enforced)

These columns store IDs of rows that live on a different server. All are declared
`unsignedBigInteger(...)->nullable()` with an `index()` for query performance, but no
`foreign()` call.

| Referencing table (connection) | Column | Referenced table (connection) | Why cross-DB |
|---|---|---|---|
| `report` (reporting) | `userID` | `users` (users) | Different engines + servers |
| `rescue` (reporting) | `caretakerID` | `users` (users) | Different engines + servers |
| `animal` (animals) | `rescueID` | `rescue` (reporting) | Different servers |
| `animal` (animals) | `slotID` | `slot` (shelter) | Different connections on same server |
| `image` (reporting) | `animalID` | `animal` (animals) | Different servers |
| `image` (reporting) | `clinicID` | `clinic` (animals) | Different servers |
| `booking` (booking) | `userID` | `users` (users) | Different engines + servers |
| `transaction` (booking) | `userID` | `users` (users) | Different engines + servers |
| `adoption` (booking) | `animalID` | `animal` (animals) | Different servers |
| `animal_booking` (booking) | `animalID` | `animal` (animals) | Different servers |
| `visit_list` (booking) | `userID` | `users` (users) | Different engines + servers |
| `visit_list_animal` (booking) | `animalID` | `animal` (animals) | Different servers |

---

## How Logical FK Integrity Is Enforced

Because the DB engine cannot enforce these constraints, the application implements three
complementary layers:

### Layer 1 — `ForeignKeyValidator` (application pre-write checks)

`app/Services/ForeignKeyValidator.php` provides static methods that query the target
connection before a write is committed:

```php
// Before creating a rescue, verify the user (caretaker) exists:
ForeignKeyValidator::validateUser($caretakerId);

// Before inserting an animal_booking row, verify the animal exists:
ForeignKeyValidator::validateAnimal($animalId);

// Check slot capacity before assigning an animal:
ForeignKeyValidator::slotHasCapacity($slotId);
```

Results are cached in Laravel's cache for 5 minutes (`CACHE_DURATION = 300`) to avoid
a validation query on every write.

### Layer 2 — DB-level triggers (intra-DB cascades and guards)

Triggers enforce rules that the DB engine *can* see locally, even when the root cause
is a cross-DB relationship. Examples:

- `trg_rescue_after_insert` (reporting): When a rescue row is inserted, automatically
  sets `report.report_status = 'Assigned'` — maintaining consistency within the same DB.
- `trg_booking_prevent_delete_with_adoptions` (booking): Blocks deletion of a `booking`
  row when `adoption` rows reference it — enforced entirely within the booking DB.
- `trg_booking_cascade_delete` (booking): Cascades booking deletion to `animal_booking`
  rows (supplements the native FK cascade already on `adoption`).
- `trg_image_before_insert` (reporting): Validates `reportID` exists in `report` (same DB),
  but explicitly cannot validate `animalID` or `clinicID` (cross-DB) — those fall to Layer 1.

### Layer 3 — Eloquent model validation and graceful fallback

Models with cross-DB relationships include fallback methods that handle the case where the
remote DB is unavailable, rather than throwing exceptions that break pages:

```php
// Animal::getImagesOrEmpty() — falls back to empty collection if reporting DB is down
public function getImagesOrEmpty(): Collection
{
    if (!app(DatabaseConnectionChecker::class)->isConnected('reporting')) {
        return collect([]);
    }
    return $this->images()->get();
}
```

---

## Orphan Risk

With no DB-enforced cascade on cross-DB logical FKs, orphaned rows are possible in
failure scenarios (e.g., an animal is deleted from `animals` while `adoption.animalID`
still holds its ID). The application mitigates this by:

1. Always deleting from the referencing side (booking/adoption) before deleting the
   referenced entity (animal) in controller logic.
2. `ForeignKeyValidator::validateAnimal()` checks before writes so invalid IDs are
   rejected before entry, not after.
3. Cache TTL of 5 minutes means stale validation passes are possible in a narrow window —
   acceptable trade-off for the performance gain.
