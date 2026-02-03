<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatusBoard;
use App\Models\StatusUpdate;
use App\Models\User;
use Carbon\Carbon;

class StatusBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users
        $admin = User::where('role', 'admin')->first();
        
        if (!$admin) {
            $this->command->warn('No admin user found. Please create an admin user first.');
            return;
        }

        $this->command->info('Seeding Status Boards...');

        // 1. Active Critical Issue - Gangguan Listrik
        $status1 = StatusBoard::create([
            'incident_number' => 'INC-2024-001',
            'title' => 'Gangguan Listrik di Hall A dan B',
            'description' => 'Terjadi pemadaman listrik mendadak di area Hall A dan Hall B akibat gangguan pada panel listrik utama. Tim teknisi sedang melakukan perbaikan.',
            'category' => 'power_outage',
            'affected_area' => 'Hall A, Hall B',
            'status' => 'investigating',
            'severity' => 'critical',
            'started_at' => Carbon::now()->subHours(2),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => true,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status1->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        StatusUpdate::create([
            'status_board_id' => $status1->id,
            'user_id' => $admin->id,
            'message' => 'Tim teknisi telah tiba di lokasi dan sedang melakukan pemeriksaan pada panel listrik utama.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subMinutes(45),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status1->id,
            'user_id' => $admin->id,
            'message' => 'Ditemukan masalah pada MCB utama. Sedang dilakukan penggantian komponen.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subMinutes(15),
        ]);

        // 2. Active High Priority - Masalah AC
        $status2 = StatusBoard::create([
            'incident_number' => 'INC-2024-002',
            'title' => 'AC Tidak Berfungsi di Ruang Meeting VIP',
            'description' => 'Sistem AC di ruang meeting VIP mengalami gangguan dan tidak mengeluarkan udara dingin. Suhu ruangan naik hingga 32°C.',
            'category' => 'facility_issue',
            'affected_area' => 'Ruang Meeting VIP',
            'status' => 'identified',
            'severity' => 'high',
            'started_at' => Carbon::now()->subHours(3),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status2->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        StatusUpdate::create([
            'status_board_id' => $status2->id,
            'user_id' => $admin->id,
            'message' => 'Masalah teridentifikasi pada kompresor AC. Menunggu teknisi HVAC untuk penanganan lebih lanjut.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subHours(1),
        ]);

        // 3. Monitoring - Jaringan Internet
        $status3 = StatusBoard::create([
            'incident_number' => 'INC-2024-003',
            'title' => 'Koneksi Internet Lambat di Area Kantor',
            'description' => 'Beberapa pengguna melaporkan koneksi internet yang lambat di area kantor. Kecepatan download turun dari 100 Mbps menjadi 20 Mbps.',
            'category' => 'network_issue',
            'affected_area' => 'Area Kantor Lt. 2',
            'status' => 'monitoring',
            'severity' => 'medium',
            'started_at' => Carbon::now()->subHours(5),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status3->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        StatusUpdate::create([
            'status_board_id' => $status3->id,
            'user_id' => $admin->id,
            'message' => 'Tim IT telah melakukan restart pada router utama. Kecepatan internet kembali normal.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subHours(3),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status3->id,
            'user_id' => $admin->id,
            'message' => 'Sedang dalam pemantauan untuk memastikan masalah tidak berulang.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // 4. Resolved - Masalah Printer
        $status4 = StatusBoard::create([
            'incident_number' => 'INC-2024-004',
            'title' => 'Printer di Ruang Admin Tidak Berfungsi',
            'description' => 'Printer HP LaserJet di ruang administrasi tidak dapat mencetak dokumen. Lampu indikator error menyala.',
            'category' => 'technical_issue',
            'affected_area' => 'Ruang Administrasi',
            'status' => 'resolved',
            'severity' => 'low',
            'started_at' => Carbon::now()->subDays(1),
            'resolved_at' => Carbon::now()->subHours(20),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status4->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
            'created_at' => Carbon::now()->subDays(1),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status4->id,
            'user_id' => $admin->id,
            'message' => 'Ditemukan kertas yang tersangkut di dalam printer. Sedang dilakukan pembersihan.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subHours(22),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status4->id,
            'user_id' => $admin->id,
            'message' => 'Masalah telah diperbaiki. Printer sudah dapat digunakan kembali dengan normal.',
            'update_type' => 'resolved',
            'created_at' => Carbon::now()->subHours(20),
        ]);

        // 5. Resolved - Elevator Maintenance
        $status5 = StatusBoard::create([
            'incident_number' => 'INC-2024-005',
            'title' => 'Maintenance Rutin Elevator',
            'description' => 'Elevator A dan B akan menjalani maintenance rutin. Diharapkan menggunakan tangga sementara waktu.',
            'category' => 'facility_issue',
            'affected_area' => 'Elevator A & B',
            'status' => 'resolved',
            'severity' => 'medium',
            'started_at' => Carbon::now()->subDays(2),
            'resolved_at' => Carbon::now()->subDays(2)->addHours(4),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status5->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status5->id,
            'user_id' => $admin->id,
            'message' => 'Maintenance elevator dimulai. Estimasi selesai 4 jam.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subDays(2)->addHour(),
        ]);

        StatusUpdate::create([
            'status_board_id' => $status5->id,
            'user_id' => $admin->id,
            'message' => 'Maintenance selesai. Kedua elevator sudah dapat digunakan kembali.',
            'update_type' => 'resolved',
            'created_at' => Carbon::now()->subDays(2)->addHours(4),
        ]);

        // 6. Active Medium - CCTV Issue
        $status6 = StatusBoard::create([
            'incident_number' => 'INC-2024-006',
            'title' => 'CCTV Area Parkir Tidak Merekam',
            'description' => 'Sistem CCTV di area parkir basement tidak merekam sejak pagi hari. Penyimpanan NVR penuh.',
            'category' => 'technical_issue',
            'affected_area' => 'Area Parkir Basement',
            'status' => 'investigating',
            'severity' => 'medium',
            'started_at' => Carbon::now()->subHours(6),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => true,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status6->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        StatusUpdate::create([
            'status_board_id' => $status6->id,
            'user_id' => $admin->id,
            'message' => 'Tim keamanan sedang melakukan pembersihan storage NVR dan backup rekaman lama.',
            'update_type' => 'update',
            'created_at' => Carbon::now()->subHours(3),
        ]);

        // 7. Low Priority - Water Dispenser
        $status7 = StatusBoard::create([
            'incident_number' => 'INC-2024-007',
            'title' => 'Dispenser Air di Pantry Kosong',
            'description' => 'Galon air di dispenser pantry lantai 3 habis dan belum diganti.',
            'category' => 'facility_issue',
            'affected_area' => 'Pantry Lt. 3',
            'status' => 'investigating',
            'severity' => 'low',
            'started_at' => Carbon::now()->subHours(1),
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'is_public' => false,
            'is_pinned' => false,
        ]);

        StatusUpdate::create([
            'status_board_id' => $status7->id,
            'user_id' => $admin->id,
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        $this->command->info('✅ Status Boards seeded successfully!');
        $this->command->info('   - Created 7 status boards');
        $this->command->info('   - Created ' . StatusUpdate::count() . ' status updates');
        $this->command->info('   - 3 Active incidents (1 Critical, 1 High, 2 Medium)');
        $this->command->info('   - 2 Monitoring incidents');
        $this->command->info('   - 2 Resolved incidents');
    }
}