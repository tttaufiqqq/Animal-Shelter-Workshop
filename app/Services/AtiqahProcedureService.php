<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AtiqahProcedureService
{
    /**
     * Set audit context variables for MySQL triggers
     */
    protected function setAuditContext(): void
    {
        $user = Auth::user();

        DB::connection('atiqah')->statement('SET @audit_user_id = ?', [$user->id ?? null]);
        DB::connection('atiqah')->statement('SET @audit_user_name = ?', [$user->name ?? null]);
        DB::connection('atiqah')->statement('SET @audit_user_email = ?', [$user->email ?? null]);
        DB::connection('atiqah')->statement('SET @audit_user_role = ?', [$user ? $user->getRoleNames()->first() : null]);
    }

    // ==========================================
    // SECTION PROCEDURES
    // ==========================================

    /**
     * Create a new section
     *
     * @param array $data ['name', 'description']
     * @return array ['success' => bool, 'section_id' => int|null, 'message' => string]
     */
    public function createSection(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_section_create(?, ?, ?, ?, ?, @o_section_id, @o_status, @o_message)', [
            $data['name'],
            $data['description'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_section_id as section_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'section_id' => $result->section_id,
            'message' => $result->message,
        ];
    }

    /**
     * Read a single section by ID
     *
     * @param int $sectionId
     * @return object|null
     */
    public function readSection(int $sectionId): ?object
    {
        DB::connection('atiqah')->statement('CALL sp_section_read(?)', [$sectionId]);

        $result = DB::connection('atiqah')->select('SELECT * FROM section WHERE id = ?', [$sectionId]);

        return $result[0] ?? null;
    }

    /**
     * Update a section
     *
     * @param int $sectionId
     * @param array $data ['name', 'description']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateSection(int $sectionId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_section_update(?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $sectionId,
            $data['name'],
            $data['description'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    /**
     * Delete a section
     *
     * @param int $sectionId
     * @return array ['success' => bool, 'has_slots' => bool, 'message' => string]
     */
    public function deleteSection(int $sectionId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_section_delete(?, ?, ?, ?, @o_has_slots, @o_status, @o_message)', [
            $sectionId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_has_slots as has_slots, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_slots' => (bool) $result->has_slots,
            'message' => $result->message,
        ];
    }

    // ==========================================
    // SLOT PROCEDURES
    // ==========================================

    /**
     * Create a new slot
     *
     * @param array $data ['name', 'sectionID', 'capacity', 'status']
     * @return array ['success' => bool, 'slot_id' => int|null, 'message' => string]
     */
    public function createSlot(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_slot_create(?, ?, ?, ?, ?, ?, ?, @o_slot_id, @o_status, @o_message)', [
            $data['name'],
            $data['sectionID'],
            $data['capacity'],
            $data['status'] ?? 'available',
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_slot_id as slot_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'slot_id' => $result->slot_id,
            'message' => $result->message,
        ];
    }

    /**
     * Read a single slot by ID
     *
     * @param int $slotId
     * @return object|null
     */
    public function readSlot(int $slotId): ?object
    {
        DB::connection('atiqah')->statement('CALL sp_slot_read(?)', [$slotId]);

        $result = DB::connection('atiqah')->select('SELECT * FROM slot WHERE id = ?', [$slotId]);

        return $result[0] ?? null;
    }

    /**
     * Update a slot
     *
     * @param int $slotId
     * @param array $data ['name', 'sectionID', 'capacity', 'status']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateSlot(int $slotId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_slot_update(?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $slotId,
            $data['name'],
            $data['sectionID'],
            $data['capacity'],
            $data['status'] ?? null,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    /**
     * Delete a slot
     *
     * @param int $slotId
     * @return array ['success' => bool, 'has_animals' => bool, 'animal_count' => int, 'message' => string]
     */
    public function deleteSlot(int $slotId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_slot_delete(?, ?, ?, ?, @o_has_animals, @o_animal_count, @o_status, @o_message)', [
            $slotId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_has_animals as has_animals, @o_animal_count as animal_count, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_animals' => (bool) $result->has_animals,
            'animal_count' => $result->animal_count ?? 0,
            'message' => $result->message,
        ];
    }

    // ==========================================
    // CATEGORY PROCEDURES
    // ==========================================

    /**
     * Create a new category
     *
     * @param array $data ['main', 'sub']
     * @return array ['success' => bool, 'category_id' => int|null, 'message' => string]
     */
    public function createCategory(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_category_create(?, ?, ?, ?, ?, @o_category_id, @o_status, @o_message)', [
            $data['main'],
            $data['sub'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_category_id as category_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'category_id' => $result->category_id,
            'message' => $result->message,
        ];
    }

    /**
     * Read a single category by ID
     *
     * @param int $categoryId
     * @return object|null
     */
    public function readCategory(int $categoryId): ?object
    {
        DB::connection('atiqah')->statement('CALL sp_category_read(?)', [$categoryId]);

        $result = DB::connection('atiqah')->select('SELECT * FROM category WHERE id = ?', [$categoryId]);

        return $result[0] ?? null;
    }

    /**
     * Update a category
     *
     * @param int $categoryId
     * @param array $data ['main', 'sub']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateCategory(int $categoryId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_category_update(?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $categoryId,
            $data['main'],
            $data['sub'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    /**
     * Delete a category
     *
     * @param int $categoryId
     * @return array ['success' => bool, 'has_inventories' => bool, 'message' => string]
     */
    public function deleteCategory(int $categoryId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_category_delete(?, ?, ?, ?, @o_has_inventories, @o_status, @o_message)', [
            $categoryId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_has_inventories as has_inventories, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'has_inventories' => (bool) $result->has_inventories,
            'message' => $result->message,
        ];
    }

    // ==========================================
    // INVENTORY PROCEDURES
    // ==========================================

    /**
     * Create a new inventory item
     *
     * @param array $data ['slotID', 'item_name', 'categoryID', 'quantity', 'weight', 'brand', 'status']
     * @return array ['success' => bool, 'inventory_id' => int|null, 'message' => string]
     */
    public function createInventory(array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_inventory_create(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_inventory_id, @o_status, @o_message)', [
            $data['slotID'],
            $data['item_name'],
            $data['categoryID'],
            $data['quantity'],
            $data['weight'] ?? null,
            $data['brand'] ?? null,
            $data['status'] ?? 'available',
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_inventory_id as inventory_id, @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'inventory_id' => $result->inventory_id,
            'message' => $result->message,
        ];
    }

    /**
     * Read a single inventory item by ID
     *
     * @param int $inventoryId
     * @return object|null
     */
    public function readInventory(int $inventoryId): ?object
    {
        DB::connection('atiqah')->statement('CALL sp_inventory_read(?)', [$inventoryId]);

        $result = DB::connection('atiqah')->select('SELECT * FROM inventory WHERE id = ?', [$inventoryId]);

        return $result[0] ?? null;
    }

    /**
     * Update an inventory item
     *
     * @param int $inventoryId
     * @param array $data ['item_name', 'categoryID', 'quantity', 'weight', 'brand', 'status']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateInventory(int $inventoryId, array $data): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_inventory_update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @o_status, @o_message)', [
            $inventoryId,
            $data['item_name'],
            $data['categoryID'],
            $data['quantity'],
            $data['weight'] ?? null,
            $data['brand'] ?? null,
            $data['status'],
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }

    /**
     * Delete an inventory item
     *
     * @param int $inventoryId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteInventory(int $inventoryId): array
    {
        $this->setAuditContext();

        $user = Auth::user();

        DB::connection('atiqah')->statement('CALL sp_inventory_delete(?, ?, ?, ?, @o_status, @o_message)', [
            $inventoryId,
            $user->id ?? null,
            $user->name ?? null,
            $user->email ?? null,
        ]);

        $result = DB::connection('atiqah')->select('SELECT @o_status as status, @o_message as message')[0];

        return [
            'success' => $result->status === 'success',
            'message' => $result->message,
        ];
    }
}
