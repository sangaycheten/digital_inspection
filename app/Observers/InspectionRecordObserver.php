<?php

namespace App\Observers;

use App\Models\InspectionRecord;

class InspectionRecordObserver
{
    /**
     * Before insert: flip the existing current record to not-current so
     * the uniqueness invariant (one current per asset) is preserved.
     * This mirrors the "UPDATE inspection_records SET is_current = false …"
     * step described in the requirements, handled at the application layer.
     */
    public function creating(InspectionRecord $record): void
    {
        if ($record->is_current) {
            InspectionRecord::where('asset_id', $record->asset_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }
    }

    /**
     * After insert: mirror the PostgreSQL trigger — push the approved result
     * up onto the parent asset so current_status stays a fast indexed read.
     */
    public function created(InspectionRecord $record): void
    {
        if ($record->document_status === 'approved' && $record->is_current) {
            $record->asset()->update([
                'current_status'        => $record->result,
                'current_inspection_id' => $record->id,
            ]);
        }
    }

    /**
     * Approved records are immutable audit entries.
     * Draft and submitted records may be updated (e.g. save-as-draft edits, approval, rejection).
     * A correction to an approved record must be a new InspectionRecord referencing previous_inspection_id.
     */
    public function updating(InspectionRecord $record): bool
    {
        if ($record->getOriginal('document_status') === 'approved') {
            throw new \LogicException('Approved inspection records are immutable. Submit a new record with previous_inspection_id set instead.');
        }
        return true;
    }

    public function deleting(InspectionRecord $record): bool
    {
        throw new \LogicException('inspection_records cannot be deleted.');
    }
}
