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

### Layer 1 — per-write-path validation (application pre-write checks)

There is no single central validator — each write path checks the specific cross-DB id it
accepts, using whichever mechanism fits that call site:

```php
// Laravel's cross-connection `exists:` validation rule, e.g. when confirming a
// booking or assigning an animal to a slot:
'animal_ids.*' => 'required|exists:animals.animal,id',
'slotID' => 'nullable|exists:shelter.slot,id',

// Inline lookup before assigning a caretaker:
$caretaker = User::findOrFail($request->caretaker_id); // on the `users` connection

// Some logical FKs (booking.userID, report.userID, visit_list.userID, ...) never
// take untrusted input at all — they're always Auth::id(), so there's nothing to
// validate.
```

(A previous iteration centralized this in `app/Services/ForeignKeyValidator.php` with
5-minute-cached static methods like `ForeignKeyValidator::validateAnimal($id)`. That class
was deleted — grep confirmed it had zero callers anywhere in `app/`; every write path
above was already doing its own validation independently. See
`tests/Feature/CrossDb/LogicalForeignKeyTest.php` for per-FK coverage of the mechanism
each one actually uses today.)

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
2. Each write path validates the specific cross-DB id it accepts before the row is created
   (see Layer 1 above) so invalid IDs are rejected before entry, not after.
3. Existing rows are not re-validated after creation — if the referenced row is deleted out
   from under a logical FK (bypassing the "delete referencing side first" convention above,
   e.g. via direct DB access), the reference goes stale with no automatic cleanup. See
   `tests/Feature/CrossDb/OrphanRiskTest.php`.
