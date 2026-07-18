# Animals Module — Page Flow

Routes: `routes/partials/animal-management.php` + `routes/web.php` (matching/profile routes).
Controller: `AnimalManagementController`, composed of `ManagesAnimals`, `ViewsAnimals`,
`ManagesClinicsVets`, `ManagesMedicalRecords`, `ManagesMatching` (+ `CalculatesMatchScore`).

## 1. Browse & view an animal (any authenticated user)

```
 [Animal List]                     [Animal Detail]
 GET /animal                       GET /animal/{animal}
 animal-management.index    ---->  animal-management.show
 (index(), animal-management/      (show(), animal-management/show.blade.php)
  main.blade.php)                  shows profile, images, medicals,
      | search/species/gender      vaccinations, slot, active bookings
      | filters, "rescued by me"        |
      | (caretaker only)                | "Add to Visit List"
      v                                 v
 [paginated animal cards]          hands off to the Booking/Adoption
                                   module (see docs/flows/booking-adoption/
                                   flow.md) — this module does not manage
                                   the visit list itself, only reads it
                                   to show "already added" state.
```

Non-admins only ever see `adoption_status = Not Adopted` animals; admins can filter by status too.

## 2. Staff: create an animal (caretaker/admin)

```
 [Rescue detail / New Animal link]
 GET /animal/create/rescue-{rescue_id?}
 animal-management.create  (create(), animal-management/create.blade.php)
 pre-fills available shelter slots + rescues to link
      |
      | fill name/weight/species/health/age/gender/rescueID/slotID,
      | upload 1+ images
      v
 POST /animal/store  ->  animal-management.store  (store())
      |
      +-- slotID given but slot not 'available'? --> back with error, no write
      |
      +-- otherwise: sp_animal_create (reporting-connection procedure)
      |     -> upload each image to Cloudinary -> Image::create() per file
      |
      v
 redirect back to animal-management.create (same rescue) with a success
 flash — deliberately loops back to the create form ("add another animal?")
 rather than to the animal list.
```

## 3. Staff: edit / delete an animal

```
 [Animal Detail] --edit-form--> PUT /animal/{animal} -> update()
      |                              |
      |                              +-- reassigning to a different, non-
      |                              |   available slot? --> back with error
      |                              +-- otherwise: sp_animal_update,
      |                                  delete/add images as requested
      |                                  (Cloudinary destroy + Image rows)
      v
 [Animal Detail] --delete-button--> DELETE /animal/{animal} -> destroy()
      |
      +-- deletes all Cloudinary images + Image rows (reporting connection)
      +-- sp_animal_delete (frees the animal row)
      +-- if the animal held a slot: recomputes that slot's occupied/
      |   available status from the remaining animal count (shelter connection)
      v
 redirect to animal-management.index with a success flash
```

## 4. Staff: assign / reassign a shelter slot

```
 [Animal Detail] --slot picker--> POST /animals/{animal}/assign-slot
                                  animals.assignSlot -> assignSlot()
      |
      +-- sp_animal_assign_slot (updates animal.slotID, returns the
      |   previous slot id)
      +-- recompute new slot's occupied/available status
      +-- if there was a previous, different slot: recompute its status
      |   too (frees it if now under capacity)
      v
 redirect back to the animal detail page with a success flash
```

## 5. Staff: clinics, vets, medical & vaccination records

```
 [Clinic/Vet Management]
 GET /clinic-vet -> animal-management.clinic-index -> indexClinic()
 (animal-management/main-manage-cv.blade.php)
      |
      +-- POST /store-clinics -> storeClinic() -> sp_clinic_create
      +-- GET/PUT/DELETE /clinics/{id} -> edit/update/destroyClinic()
      |     (editClinic() returns JSON for an in-page edit modal)
      +-- POST /store-vets -> storeVet() -> sp_vet_create (must reference
      |     an existing clinicID)
      +-- GET/PUT/DELETE /vets/{id} -> edit/update/destroyVet()

 [Animal Detail] --"Add Medical Record"--> GET /medical-create
      (routes to the same indexClinic() view — the form posts back to the
       animal it was opened from)
      |
      +-- POST /medical-records/store -> storeMedical() -> sp_medical_create
      +-- POST /vaccination-records/store -> storeVaccination()
            -> sp_vaccination_create (rejects a next_due_date in the past)
```

## 6. Adopter: view computed matches & manage a profile

```
 [Adopter Profile Form]                    [Match List]
 POST /adopter/profile/store          GET /animal-matches
 adopter.profile.store                animal.matches -> getMatches()
 (ProfileController::storeOrUpdate,        |
  users connection)                        +-- 503 if users or animals DB
      |                                    |   is offline
      | upserts fn_user_upsert_            +-- 200 with a "complete your
      | adopter_profile, invalidates       |   profile" message if no
      | this user's cached matches         |   AdopterProfile exists yet
      v                                    |
 [Match List reloads] <--------------------+
                                           +-- cache hit (5 min, unless
                                           |   ?force_refresh=1) -> return
                                           |   cached JSON
                                           +-- else: fetch up to 20 "Not
                                               Adopted" animals with a
                                               profile, score each, return
                                               the top 5 by score (JSON,
                                               consumed by the frontend's
                                               own match-list JS/component)

 [Animal Profile Form] (caretaker/admin, per-animal size/energy/temperament/
 medical_needs/good_with_kids/good_with_pets)
 POST /animal/profile/store/{animal} -> animal.profile.store -> storeOrUpdate()
      -> sp_animal_profile_upsert (animals connection) — this is the data
         getMatches() scores against; an animal with no profile row is
         invisible to matching entirely (whereHas('profile') filter).
```
