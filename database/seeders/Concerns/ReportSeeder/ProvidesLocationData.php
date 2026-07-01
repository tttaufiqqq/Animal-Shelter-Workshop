<?php

namespace Database\Seeders\Concerns\ReportSeeder;

trait ProvidesLocationData
{
    private function getMalaccaLocations(): array
    {
        return [
            // CENTRAL MALACCA (Tourist & Commercial Areas)
            ['lat' => 2.1964, 'lng' => 102.2487, 'address' => 'Dataran Pahlawan Megamall, Jalan Merdeka', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1953, 'lng' => 102.2493, 'address' => 'Jonker Street, Jalan Hang Jebat', 'area' => 'Chinatown'],
            ['lat' => 2.1896, 'lng' => 102.2489, 'address' => 'A Famosa, Jalan Kota', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1946, 'lng' => 102.2486, 'address' => 'Stadthuys, Jalan Gereja', 'area' => 'Dutch Square'],
            ['lat' => 2.1945, 'lng' => 102.2487, 'address' => 'Christ Church, Jalan Gereja', 'area' => 'Dutch Square'],
            ['lat' => 2.1921, 'lng' => 102.2493, 'address' => 'St. Paul\'s Hill, Jalan Kota', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1989, 'lng' => 102.2511, 'address' => 'Mahkota Parade, Jalan Merdeka', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1972, 'lng' => 102.2509, 'address' => 'Hatten Square, Jalan Merdeka', 'area' => 'Bandar Hilir'],
            // RESIDENTIAL AREAS
            ['lat' => 2.2018, 'lng' => 102.2537, 'address' => 'Taman Melaka Raya, Jalan Taman Melaka Raya', 'area' => 'Melaka Raya'],
            ['lat' => 2.2346, 'lng' => 102.2494, 'address' => 'Bukit Beruang, Jalan Bukit Beruang', 'area' => 'Bukit Beruang'],
            ['lat' => 2.1872, 'lng' => 102.2563, 'address' => 'Taman Kota Laksamana, Jalan Kota Laksamana', 'area' => 'Kota Laksamana'],
            ['lat' => 2.2502, 'lng' => 102.2511, 'address' => 'Batu Berendam, Jalan Batu Berendam', 'area' => 'Batu Berendam'],
            ['lat' => 2.2189, 'lng' => 102.2635, 'address' => 'Bertam Ulu, Jalan Bertam', 'area' => 'Bertam'],
            ['lat' => 2.2156, 'lng' => 102.2489, 'address' => 'Taman Limbongan, Jalan Limbongan', 'area' => 'Limbongan'],
            ['lat' => 2.2089, 'lng' => 102.2456, 'address' => 'Bachang, Jalan Bachang', 'area' => 'Bachang'],
            ['lat' => 2.2067, 'lng' => 102.2621, 'address' => 'Taman Teknologi, Jalan Teknologi', 'area' => 'Melaka Tengah'],
            // MARKETS & COMMERCIAL
            ['lat' => 2.1978, 'lng' => 102.2501, 'address' => 'Pasar Besar Melaka, Jalan Hang Kasturi', 'area' => 'Bandar Hilir'],
            ['lat' => 2.1885, 'lng' => 102.2571, 'address' => 'Pasar Malam Kota Laksamana, Taman Kota Laksamana', 'area' => 'Kota Laksamana'],
            ['lat' => 2.2024, 'lng' => 102.2539, 'address' => 'Mydin Mall Melaka, Jalan Tun Ali', 'area' => 'Melaka Raya'],
            ['lat' => 2.2134, 'lng' => 102.2467, 'address' => 'Plaza Mahkota, Jalan Laksamana', 'area' => 'Bandar Hilir'],
            // SUBURBAN AREAS
            ['lat' => 2.2682, 'lng' => 102.2806, 'address' => 'Ayer Keroh, Jalan Ayer Keroh', 'area' => 'Ayer Keroh'],
            ['lat' => 2.3036, 'lng' => 102.2894, 'address' => 'Durian Tunggal, Jalan Durian Tunggal', 'area' => 'Durian Tunggal'],
            ['lat' => 2.2794, 'lng' => 102.2650, 'address' => 'Sungai Udang, Jalan Sungai Udang', 'area' => 'Sungai Udang'],
            ['lat' => 2.2095, 'lng' => 102.1979, 'address' => 'Klebang, Jalan Klebang Besar', 'area' => 'Klebang'],
            ['lat' => 2.2278, 'lng' => 102.1967, 'address' => 'Tanjung Kling, Jalan Tanjung Kling', 'area' => 'Tanjung Kling'],
            ['lat' => 2.2445, 'lng' => 102.2678, 'address' => 'Paya Rumput, Jalan Paya Rumput', 'area' => 'Paya Rumput'],
            // INDUSTRIAL & MIXED
            ['lat' => 2.2567, 'lng' => 102.2489, 'address' => 'Taman Merdeka, Jalan Merdeka', 'area' => 'Batu Berendam'],
            ['lat' => 2.2389, 'lng' => 102.2567, 'address' => 'Taman Bukit Rambai, Jalan Bukit Rambai', 'area' => 'Bukit Rambai'],
            ['lat' => 2.2123, 'lng' => 102.2734, 'address' => 'Taman Cheng, Jalan Cheng', 'area' => 'Cheng'],
            // EDUCATIONAL & PUBLIC FACILITIES
            ['lat' => 2.3108, 'lng' => 102.3184, 'address' => 'UTeM, Hang Tuah Jaya', 'area' => 'Durian Tunggal'],
            ['lat' => 2.1892, 'lng' => 102.2584, 'address' => 'Hospital Melaka, Jalan Mufti Haji Khalil', 'area' => 'Melaka Tengah'],
            ['lat' => 2.2683, 'lng' => 102.2515, 'address' => 'Zoo Melaka, Lebuh Ayer Keroh', 'area' => 'Ayer Keroh'],
            ['lat' => 2.2456, 'lng' => 102.2523, 'address' => 'Taman Tasik Utama, Jalan Tasik Utama', 'area' => 'Ayer Keroh'],
            // BEACH & COASTAL AREAS
            ['lat' => 2.2123, 'lng' => 102.1956, 'address' => 'Pantai Klebang, Jalan Pantai Klebang', 'area' => 'Klebang'],
            ['lat' => 2.3269, 'lng' => 102.2142, 'address' => 'Tanjung Bidara Beach, Jalan Tanjung Bidara', 'area' => 'Tanjung Bidara'],
            ['lat' => 2.2198, 'lng' => 102.1989, 'address' => 'Pantai Puteri, Jalan Pantai Puteri', 'area' => 'Klebang'],
            // VILLAGES & KAMPUNG AREAS
            ['lat' => 2.2734, 'lng' => 102.2456, 'address' => 'Kampung Tanjung Minyak, Jalan Tanjung Minyak', 'area' => 'Tanjung Minyak'],
            ['lat' => 2.2567, 'lng' => 102.2389, 'address' => 'Kampung Serkam, Jalan Serkam', 'area' => 'Serkam'],
            ['lat' => 2.2912, 'lng' => 102.2723, 'address' => 'Kampung Duyong, Jalan Duyong', 'area' => 'Duyong'],
            ['lat' => 2.2645, 'lng' => 102.2834, 'address' => 'Kampung Tengah, Jalan Tengah', 'area' => 'Ayer Keroh'],
            // ADDITIONAL RESIDENTIAL HOTSPOTS
            ['lat' => 2.1923, 'lng' => 102.2612, 'address' => 'Taman Malim Jaya, Jalan Malim Jaya', 'area' => 'Malim'],
            ['lat' => 2.2234, 'lng' => 102.2578, 'address' => 'Taman Bertam Jaya, Jalan Bertam Jaya', 'area' => 'Bertam'],
            ['lat' => 2.2456, 'lng' => 102.2612, 'address' => 'Taman Bukit Katil, Jalan Bukit Katil', 'area' => 'Bukit Katil'],
        ];
    }
}
