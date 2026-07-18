# Shelter Module — Page Flow

Source: `routes/partials/shelter-management.php`, `ShelterManagementController` (composes
`ManagesSections`, `ManagesSlots`, `ManagesCategories`, `ManagesInventory`, `ViewsDetails`),
`resources/views/shelter-management/*`.

One route restriction for the whole module: `auth` only — no role-specific middleware gates any of
these routes today.

Everything lives on a single page, `shelter-management.index` (`GET /shelter-management/slots`),
with four tabs (Sections / Categories / Slots / Inventory) switched client-side
(`view-tabs.blade.php`). Create/edit/delete all happen through modals over AJAX — no full-page
navigation for any of the CRUD actions below; only the initial page load and the animal-details
drill-down go through a real request cycle for their *data*, still without leaving the page.

## Sections

```
[ Index page, Sections tab ]
        |
        |  click "Add Section"
        v
[ section-modal.blade.php ]  --(POST /shelter-management/sections)--> storeSection()
        |                                                                    |
        |  click "Edit" on a row                                            v
        v                                                          redirect back + flash
GET /shelter-management/sections/{id}/edit  --> editSection() --> JSON --> pre-fills the same modal
        |
        |  submit
        v
PUT /shelter-management/sections/{id} --> updateSection() --> redirect back + flash

[ Index page ] --(click "Delete")--> confirmation-modal.blade.php
        |
        v
DELETE /shelter-management/sections/{id} --> deleteSection() --> redirect back + flash
```

## Categories

Same shape as Sections (`category-modal.blade.php`, `category-detail-modal.blade.php`):
create -> `POST /shelter-management/categories`, edit -> `GET .../edit` (JSON) then
`PUT /shelter-management/categories/{id}`, delete -> `DELETE /shelter-management/categories/{id}`.

## Slots

```
[ Index page, Slots tab ]
        |
        |  click "Add Slot"
        v
[ slot-modal.blade.php ] --(POST /shelter-management/slots)--> storeSlot()
        |                        status is always forced to 'available' on create
        |
        |  click a slot card / "View Details"
        v
GET /shelter-management/slots/{id}/details --> getSlotDetails()
        |     returns section, capacity, status, every animal currently in the slot
        |     (with vaccination/medical counts) and every inventory item stored in it
        v
[ slot-detail-modal.blade.php ] --(click "View Animal")--> GET /shelter-management/animals/{id}/details
                                                             --> getAnimalDetails() --> animal-detail-modal.blade.php

[ Index page ] --(click "Edit")--> GET .../edit (JSON) --> slot-modal.blade.php (pre-filled)
        |
        v
PUT /shelter-management/slots/{id} --> updateSlot() --> recomputes status (see activity-diagram.md)

[ Index page ] --(click "Delete")--> confirmation-modal.blade.php
        |
        v
DELETE /shelter-management/slots/{id} --> deleteSlot() --> blocked if animals still assigned
```

A slot's `status` is not only changed from this page — assigning or removing an animal from a slot
in the **Animals module** also recomputes/affects slot occupancy (see `docs/flows/animals/flow.md`).
This module's own update form recomputes it too, using its own live animal count at submit time.

## Inventory

```
[ Index page, Inventory tab ]
        |
        |  click "Add Inventory" (from a slot's detail modal, or the tab directly)
        v
[ inventory-create-modal.blade.php ] --(POST /shelter-management/inventory)--> storeInventory()
        |
        |  click "View Details" on a row
        v
GET /shelter-management/inventory/{id}/details --> getInventoryDetails() --> inventory-detail-modal.blade.php
        |
        |  click "Edit" (from the same modal)
        v
PUT /shelter-management/inventory/{id} --> updateInventory() --> redirect back + flash

[ Index page ] --(click "Delete")--> confirmation-modal.blade.php
        |
        v
DELETE /shelter-management/inventory/{id} --> deleteInventory() --> redirect back + flash
```

`inventory-create-modal/recommendation-functions.blade.php` renders some client-side-only
suggestion text in the create form — there is no backend recommendation engine behind it (grep
confirms zero matches for "recommend" anywhere in `app/`); don't read more into that UI than a
static hint.
