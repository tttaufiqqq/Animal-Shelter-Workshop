<?php

namespace Database\Seeders\Concerns\RescueSeeder;

use Carbon\Carbon;
use App\Models\Rescue;

trait BuildsRescues
{
    private function buildRescues($reports, array $caretakers): array
    {
        $successRemarks = [
            'Animal(s) successfully rescued and brought to shelter. All animals are in stable condition.',
            'Rescue operation completed. Animals secured and transported safely to the facility.',
            'Successfully rescued and relocated animals to shelter. Initial health check completed.',
            'Animals rescued without complications. Currently under observation at the shelter.',
            'Rescue mission accomplished. All animals have been safely recovered and are receiving care.',
            'Operation successful. Animals are now safe at the shelter and receiving medical attention.',
            'Animals successfully rescued from reported location. No injuries sustained during rescue.',
            'Rescue completed successfully. Animals are calm and adapting well to shelter environment.',
            'All animals safely rescued and transported. Veterinary assessment scheduled.',
            'Successful rescue operation. Animals are healthy and have been assigned to shelter sections.',
        ];
        $failedRemarks = [
            'Animals could not be located at the reported address. Area searched thoroughly.',
            'Rescue operation failed. Animals had already left the location before team arrival.',
            'Unable to complete rescue due to dangerous location conditions. Will retry with proper equipment.',
            'Animals were too scared and fled before rescue team could secure them safely.',
            'Location access denied by property owner. Legal intervention required.',
            'Rescue attempt failed. Animals were already rescued by another organization.',
            'Could not locate animals despite multiple search attempts in the reported area.',
            'Weather conditions made rescue unsafe. Operation postponed for animal and team safety.',
            'Animals in location too aggressive to approach safely. Specialist team required.',
            'Report deemed inaccurate upon arrival. No animals found at specified location.',
        ];
        $scheduledRemarks = [
            'Rescue operation scheduled. Team will be dispatched within 24-48 hours.',
            'Rescue date and time confirmed. Caretaker team assigned and notified.',
            'Operation planned for next available window. Resources being prepared.',
            'Scheduled for rescue. Awaiting optimal conditions and team availability.',
        ];
        $inProgressRemarks = [
            'Rescue team currently on-site. Operation in progress.',
            'Caretakers are actively working to secure the animals safely.',
            'Rescue operation underway. Team is assessing situation and planning approach.',
            'In the process of rescuing animals. Updates will be provided upon completion.',
        ];

        $rescues      = [];
        $statusCounts = ['Success' => 0, 'Failed' => 0, 'Scheduled' => 0, 'In Progress' => 0];

        foreach ($reports as $report) {
            if (rand(1, 100) <= 20) {
                continue; // 20% of reports stay pending (no rescue)
            }

            if (rand(1, 100) <= 40) {
                $status  = 'Success';
                $remarks = $successRemarks[array_rand($successRemarks)];
                $statusCounts['Success']++;
            } else {
                $statusOptions = ['Failed' => 30, 'Scheduled' => 25, 'In Progress' => 45];
                $rand          = rand(1, 100);
                $cumulative    = 0;
                $status        = 'In Progress';

                foreach ($statusOptions as $statusOption => $probability) {
                    $cumulative += $probability;
                    if ($rand <= $cumulative) {
                        $status = $statusOption;
                        break;
                    }
                }

                $remarks = match ($status) {
                    'Failed'      => $failedRemarks[array_rand($failedRemarks)],
                    'Scheduled'   => $scheduledRemarks[array_rand($scheduledRemarks)],
                    default       => $inProgressRemarks[array_rand($inProgressRemarks)],
                };

                $statusCounts[$status]++;
            }

            $rescueDate = Carbon::parse($report->created_at)->addHours(rand(1, 48));
            $priority   = Rescue::getPriorityFromDescription($report->description);

            $rescues[] = [
                'status'      => $status,
                'priority'    => $priority,
                'remarks'     => $remarks,
                'reportID'    => $report->id,
                'caretakerID' => $caretakers[array_rand($caretakers)],
                'created_at'  => $rescueDate,
                'updated_at'  => $rescueDate,
            ];
        }

        return [$rescues, $statusCounts];
    }
}
