<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Resident;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use App\Models\CommunityConcern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BarangayInformationSeeder extends Seeder
{
    public function run(): void
    {
        // Create the administrator account.
        $admin = User::updateOrCreate(
            ['email' => 'admin@barangay.test'],
            [
                'name' => 'Barangay Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
            ]
        );

        // Create a resident login account.
        $residentUser = User::updateOrCreate(
            ['email' => 'resident@barangay.test'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('Resident123!'),
                'role' => 'resident',
            ]
        );

        // Create sample residents.
        $names = [
            ['Juan', 'Dela Cruz'],
            ['Maria', 'Santos'],
            ['Jose', 'Reyes'],
            ['Ana', 'Garcia'],
            ['Pedro', 'Mendoza'],
            ['Rosa', 'Bautista'],
            ['Carlo', 'Ramos'],
            ['Liza', 'Aquino'],
            ['Mark', 'Castillo'],
            ['Ella', 'Flores'],
            ['Miguel', 'Navarro'],
            ['Sofia', 'Torres'],
            ['Daniel', 'Villanueva'],
            ['Angela', 'Fernandez'],
            ['Paolo', 'Gonzales'],
            ['Grace', 'Rivera'],
            ['Joshua', 'Morales'],
            ['Nicole', 'Diaz'],
            ['Adrian', 'Cruz'],
            ['Patricia', 'Lopez'],
        ];

        $residents = [];

        foreach ($names as $index => [$first, $last]) {
            $data = [
                'first_name' => $first,
                'last_name' => $last,
                'address' => 'Sample Street ' . ($index + 1),
                'purok' => 'Purok ' . (($index % 5) + 1),
                'residency_status' => 'Active',
            ];

            if ($index === 0) {
                $resident = Resident::updateOrCreate(
                    ['user_id' => $residentUser->id],
                    array_merge($data, [
                        'middle_name' => 'Sample',
                        'birth_date' => '1995-05-15',
                        'sex' => 'Male',
                        'civil_status' => 'Single',
                        'contact_number' => '09000000001',
                    ])
                );
            } else {
                $resident = Resident::updateOrCreate(
                    [
                        'first_name' => $first,
                        'last_name' => $last,
                        'address' => $data['address'],
                    ],
                    array_merge($data, [
                        'middle_name' => null,
                        'birth_date' => '1990-01-01',
                        'sex' => null,
                        'civil_status' => null,
                        'contact_number' => null,
                    ])
                );
            }

            $residents[] = $resident;
        }

        // Create document types.
        $types = [
            [
                'name' => 'Barangay Clearance',
                'description' => 'Sample barangay clearance',
                'fee' => 50,
            ],
            [
                'name' => 'Certificate of Residency',
                'description' => 'Sample proof of residency',
                'fee' => 30,
            ],
            [
                'name' => 'Certificate of Indigency',
                'description' => 'Sample certificate of indigency',
                'fee' => 0,
            ],
        ];

        $documentTypes = [];

        foreach ($types as $type) {
            $documentTypes[] = DocumentType::updateOrCreate(
                ['name' => $type['name']],
                array_merge($type, ['is_active' => true])
            );
        }

        // Create 10 sample document requests.
        $statuses = [
            'Pending',
            'Approved',
            'Released',
            'Rejected',
            'Pending',
            'Approved',
            'Pending',
            'Released',
            'Rejected',
            'Pending',
        ];

        foreach (range(0, 9) as $i) {
            $status = $statuses[$i];

            DocumentRequest::updateOrCreate(
                [
                    'resident_id' => $residents[$i]->id,
                    'document_type_id' => $documentTypes[$i % 3]->id,
                    'purpose' => 'Sample purpose ' . ($i + 1),
                ],
                [
                    'status' => $status,
                    'remarks' => $status === 'Rejected'
                        ? 'Sample rejection reason'
                        : null,
                    'processed_by' => $status === 'Pending'
                        ? null
                        : $admin->id,
                    'requested_at' => now()->subDays($i),
                    'released_at' => $status === 'Released'
                        ? now()->subDays($i)
                        : null,
                ]
            );
        }

        // Create 8 sample community concerns.
        $concerns = [
            ['Road maintenance', 'Sample concern about road maintenance.'],
            ['Street lighting', 'Sample concern about street lighting.'],
            ['Waste collection', 'Sample concern about waste collection.'],
            ['Drainage', 'Sample concern about drainage.'],
            ['Noise complaint', 'Sample concern about neighborhood noise.'],
            ['Water supply', 'Sample concern about water supply.'],
            ['Public facility', 'Sample concern about a public facility.'],
            ['Community safety', 'Sample concern about community safety.'],
        ];

        foreach ($concerns as $i => [$subject, $description]) {
            $status = $i < 5 ? 'Open' : 'Resolved';

            CommunityConcern::updateOrCreate(
                [
                    'resident_id' => $residents[$i]->id,
                    'subject' => $subject,
                ],
                [
                    'description' => $description,
                    'category' => 'General',
                    'status' => $status,
                    'resolution' => $status === 'Resolved'
                        ? 'Sample resolution for testing.'
                        : null,
                    'handled_by' => $status === 'Resolved'
                        ? $admin->id
                        : null,
                ]
            );
        }
    }
}
