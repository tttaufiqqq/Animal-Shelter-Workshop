<?php

namespace App\Services;

use App\Services\Concerns\BookingProcedure\ManagesAdoptionTransactionProcedures;
use App\Services\Concerns\BookingProcedure\ManagesBookingProcedures;
use App\Services\Concerns\BookingProcedure\ManagesPivotProcedures;
use App\Services\Concerns\BookingProcedure\ManagesVisitListProcedures;

/**
 * Service for calling stored procedures on the booking (MariaDB) connection.
 *
 * Calling convention:
 *   OUT params use MariaDB session variables — call the procedure with @o_var
 *   placeholders, then SELECT those session variables in a follow-up query.
 *
 *   Procedures that only return a result set use DB::select('CALL sp(?)').
 */
class BookingProcedureService
{
    use ManagesBookingProcedures,
        ManagesVisitListProcedures,
        ManagesAdoptionTransactionProcedures,
        ManagesPivotProcedures;
}
