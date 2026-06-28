<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClinicVetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Clinics and Vets are stored in Shafiqah's database (Animal Management Module)
     */
    public function run(): void
    {
        $this->command->info('Starting Clinic & Vet Seeder...');
        $this->command->info('========================================');

        $now = Carbon::now();

        // Create Clinics
        $clinics = [
            [
                'name' => 'Seremban Veterinary Center',
                'address' => 'Jalan Tuanku Munawir, Seremban, Negeri Sembilan',
                'contactNum' => '06-762 3456',
                'latitude' => 2.7258,
                'longitude' => 101.9424,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pet Care Clinic Rasah',
                'address' => 'Taman Rasah Jaya, Seremban',
                'contactNum' => '06-765 8901',
                'latitude' => 2.7089,
                'longitude' => 101.9345,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Animal Hospital Senawang',
                'address' => 'Senawang Industrial Park, Seremban',
                'contactNum' => '06-678 2345',
                'latitude' => 2.6543,
                'longitude' => 101.9876,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Guardian Vet Clinic',
                'address' => 'Jalan Lobak, Seremban',
                'contactNum' => '06-761 4567',
                'latitude' => 2.7312,
                'longitude' => 101.9512,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Use transaction for Shafiqah's database
        DB::connection('animals')->beginTransaction();

        try {
            $this->command->info('Inserting clinics into Shafiqah\'s database...');

            // Insert clinics into Shafiqah's database
            DB::connection('animals')->table('clinic')->insert($clinics);

            // Get inserted clinic IDs from Shafiqah's database
            $clinicIds = DB::connection('animals')->table('clinic')->pluck('id')->toArray();

        // Create Vets assigned to clinics
        $vets = [
            // Vets for Seremban Veterinary Center
            [
                'name' => 'Dr. Ahmad Faizal',
                'email' => 'ahmad.faizal@serembanvet.com',
                'contactNum' => '012-345 6789',
                'specialization' => 'General Practice',
                'license_no' => 'VET-MY-2018-0234',
                'clinicID' => $clinicIds[0],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dr. Siti Nurhaliza',
                'email' => 'siti.n@serembanvet.com',
                'contactNum' => '013-456 7890',
                'specialization' => 'Surgery',
                'license_no' => 'VET-MY-2019-0567',
                'clinicID' => $clinicIds[0],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Vets for Pet Care Clinic Rasah
            [
                'name' => 'Dr. Rajesh Kumar',
                'email' => 'rajesh.k@petcarerasah.com',
                'contactNum' => '014-567 8901',
                'specialization' => 'Internal Medicine',
                'license_no' => 'VET-MY-2020-0891',
                'clinicID' => $clinicIds[1],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dr. Emily Tan',
                'email' => 'emily.tan@petcarerasah.com',
                'contactNum' => '015-678 9012',
                'specialization' => 'Exotic Animals',
                'license_no' => 'VET-MY-2021-1234',
                'clinicID' => $clinicIds[1],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Vets for Animal Hospital Senawang
            [
                'name' => 'Dr. Lim Wei Jian',
                'email' => 'weijian.lim@ahsenawang.com',
                'contactNum' => '016-789 0123',
                'specialization' => 'Emergency Care',
                'license_no' => 'VET-MY-2017-0456',
                'clinicID' => $clinicIds[2],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dr. Nurul Ain',
                'email' => 'nurul.ain@ahsenawang.com',
                'contactNum' => '017-890 1234',
                'specialization' => 'Dentistry',
                'license_no' => 'VET-MY-2022-0789',
                'clinicID' => $clinicIds[2],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Vets for Guardian Vet Clinic
            [
                'name' => 'Dr. Wong Mei Ling',
                'email' => 'meiling.wong@guardianvet.com',
                'contactNum' => '018-901 2345',
                'specialization' => 'Cardiology',
                'license_no' => 'VET-MY-2019-0321',
                'clinicID' => $clinicIds[3],
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Dr. Mohamad Rashid',
                'email' => 'rashid.m@guardianvet.com',
                'contactNum' => '019-012 3456',
                'specialization' => 'Dermatology',
                'license_no' => 'VET-MY-2020-0654',
                'clinicID' => $clinicIds[3],
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

            $this->command->info('Inserting vets into Shafiqah\'s database...');

            // Insert vets into Shafiqah's database
            DB::connection('animals')->table('vet')->insert($vets);

            DB::connection('animals')->commit();

            $this->command->info('');
            $this->command->info('=================================');
            $this->command->info('✓ Clinic & Vet Seeding Completed!');
            $this->command->info('=================================');
            $this->command->info('Total clinics created: ' . count($clinics));
            $this->command->info('Total vets created: ' . count($vets));
            $this->command->info('Database: Shafiqah (MySQL)');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::connection('animals')->rollBack();

            $this->command->error('');
            $this->command->error('Error seeding clinics and vets: ' . $e->getMessage());
            $this->command->error('Transaction rolled back');

            throw $e;
        }
    }
}
