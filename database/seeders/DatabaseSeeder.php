<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key constraints during clean reseeding
        Schema::disableForeignKeyConstraints();
        ProjectImage::truncate();
        Project::truncate();
        Inquiry::truncate();
        Schema::enableForeignKeyConstraints();

        // Seed default administrator account idempotently
        User::updateOrCreate(
            ['email' => 'admin@studiokode.com'],
            [
                'name' => 'Admin StudioKode',
                'password' => Hash::make('password'),
            ]
        );

        $projects = [
            [
                'title' => 'The Moss & Stone Sanctuary',
                'slug' => 'moss-stone-sanctuary',
                'client' => 'Private Collector',
                'location' => 'Kyoto, Japan',
                'year' => 2024,
                'area_size' => '380 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A meditative Japanese courtyard garden balancing ancient lichen-encrusted granite, velvet bryophyte carpets, and blackened cedar pavilions.',
                'description' => "Designed as an intimate contemplative sanctuary within Kyoto's historic outskirts. The site required careful soil conditioning to sustain indigenous Polytrichum commune moss species.\n\nWeathered megalithic stones sourced from Tamba quarries define the central dry-creek rill, flanked by pruned Japanese Stewartia and weeping maples. Rainwater from the cedar pavilion is guided into a hand-carved stone tsukubai basin, creating subtle acoustic ripples throughout the courtyard.\n\nAt twilight, concealed brass luminaires cast soft shadows through bamboo screens, accentuating the undulating topography of green moss hummocks against dark river pebbles.",
                'is_featured' => true,
                'published_at' => now()->subDays(12),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1579783900882-c0d3dad7b119?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Entrance stone walkway embedded in lush velvet moss margins',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1545239351-ef35f43d514b?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Hand-carved granite water basin and bamboo water flume',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1513836279014-a89f7a76ae86?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Acer palmatum canopy casting dappled shadows on stone paving',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Evening illumination along the dry-creek granite rill',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Verdant Terrace Estate',
                'slug' => 'verdant-terrace-estate',
                'client' => 'Villa Bellini Trust',
                'location' => 'Lake Como, Italy',
                'year' => 2023,
                'area_size' => '1,250 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'Cascading limestone terraces descending toward Lake Como, punctuated by columnar cypresses, fragrant citrus groves, and neoclassical water rills.',
                'description' => "Revitalizing a historic 19th-century hillside estate, this landscape masterplan reconciles severe steep topography with elegant multi-tier living spaces.\n\nHand-dressed Moltrasio stone walls support olive orchards, lavender terraces, and stepped water runnels that create acoustic serenity. The lowest terrace features a sunken infinity reflection pool visually dissolving into the lake.\n\nMicroclimate analysis guided the positioning of lemon pergolas against heat-retaining stone abutments, while native drought-hardy Mediterranean perennials ensure seasonal fragrance without intensive chemical interventions.",
                'is_featured' => true,
                'published_at' => now()->subDays(45),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Panoramic terrace view across Lake Como and alpine foothills',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1524813686514-a57563d77d61?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Symmetrical cypress promenade and tiered Moltrasio stone steps',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1533759413974-9e15f3b745ac?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Sunken limestone reflecting pool surrounded by fragrant lavender',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Citrus grove pergola with reclaimed chestnut timber beams',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Atelier Botanica Courtyard',
                'slug' => 'atelier-botanica-courtyard',
                'client' => 'Studio K Architects',
                'location' => 'Melbourne, Australia',
                'year' => 2024,
                'area_size' => '180 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A sculptural micro-climate sanctuary within an urban heritage envelope, showcasing architectural tree ferns and blackened steel reflecting pools.',
                'description' => "Concealed within a Victorian brick warehouse conversion, this compact courtyard functions as an ethereal living lung for a contemporary design atelier.\n\nMulti-layered canopy plantings of Dicksonia antarctica tree ferns establish vertical softness against textured industrial masonry. Custom raw blackened steel water mirrors cool the ambient air temperature, while permeable bluestone gravel ensures complete on-site rainwater infiltration.\n\nSubtle under-deck lighting reveals intricate frond silhouettes during nocturnal hours, transforming the workspace into a tranquil botanical immersion.",
                'is_featured' => true,
                'published_at' => now()->subDays(5),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1463936575829-25148e1db1b8?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Canopy layer of Dicksonia antarctica architectural tree ferns',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Blackened steel reflecting pool mirroring heritage red brickwork',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1507089947368-19c1da9775ae?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Bluestone paving details under filtered botanical light',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'title' => 'Highland Mist Alpine Garden',
                'slug' => 'highland-mist-alpine-garden',
                'client' => 'Dr. Hans & Elena Weber',
                'location' => 'Zurich, Switzerland',
                'year' => 2022,
                'area_size' => '850 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A biodiverse montane rockery and wild alpine meadow, embracing glacial erratic stones and cold-hardy botanical tapestries.',
                'description' => "Perched on a sub-alpine ridge overlooking the lake basin, this garden embraces severe winter frost and intense summer radiation.\n\nGlacial erratic gneiss rocks discovered during foundation excavation were repositioned to anchor micro-topographies. Planted with dryas, edelweiss, creeping wild thyme, dwarf mountain pines (Pinus mugo), and naturalized birch groves, the scheme requires zero synthetic irrigation.\n\nThe planting plan creates critical high-altitude pollinator corridors and provides dynamic seasonal metamorphosis from spring snowmelt to autumn gold.",
                'is_featured' => false,
                'published_at' => now()->subDays(80),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Alpine rock garden enveloped in morning mountain mist',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Native Pinus mugo and silver birch grove boundary',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Sunken granite fire gathering ring overlooking the valley',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'title' => 'Oasis Dune Residence',
                'slug' => 'oasis-dune-residence',
                'client' => 'Vance & Morgan Design',
                'location' => 'Palm Springs, California',
                'year' => 2023,
                'area_size' => '620 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1509316975850-ff9c5deb0cd9?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'Sculptural desert xeriscaping harmonizing rammed-earth windbreaks, specimen agaves, and pale gravel swales with mid-century lines.',
                'description' => "Responding to the extreme diurnal temperature shifts of the Sonoran Desert, this landscape celebrates the bold sculptural silhouettes of arid botanical species.\n\nMassive rammed-earth retaining walls buffer harsh seasonal desert winds and cast long geometric shadows across polished concrete patios. A curated collection of specimen blue glow agaves, golden barrel cacti, and thornless palo verde trees rise from decomposed granite beds designed for ephemeral flash-rain harvesting.\n\nDusk brings the space alive as architectural up-lighting transforms the desert spires into dramatic sculptural monuments against the San Jacinto mountains.",
                'is_featured' => false,
                'published_at' => now()->subDays(60),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Specimen golden barrel cacti framing rammed-earth wall',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Palo verde canopy casting fine shadows on pale granite gravel',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Architectural Agave attenuata grouping along concrete steps',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1470240731273-7821a6eeb6bd?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Dusk illumination across desert swale landscape',
                        'sort_order' => 4,
                    ],
                ],
            ],
            [
                'title' => 'Coastal Dune & Pine Refuge',
                'slug' => 'coastal-dune-pine-refuge',
                'client' => 'Herdade da Costa',
                'location' => 'Comporta, Portugal',
                'year' => 2024,
                'area_size' => '2,100 m²',
                'cover_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
                'masterplan_image' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80',
                'excerpt' => 'A vast coastal sanctuary celebrating maritime pines, wild dune grass stabilization, and weathered timber boardwalks navigating sandy terrain.',
                'description' => "Spanning over two hectares along the pristine Atlantic littoral, this sensitive coastal restoration preserves and expands fragile sand dune ecologies.\n\nElevated untreated ipe timber boardwalks hover gently above shifting white sands, guiding visitors between naturalized clusters of umbrella pine (Pinus pinea), sea lavender, and aromatic curry plants. A biological natural swimming pond filtered entirely by indigenous wetland reeds anchors the central living area without chlorine or synthetic additives.\n\nThe resulting sanctuary is an understated architectural homage to the raw maritime atmosphere of the Alentejo coast.",
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'images' => [
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Floating timber boardwalk winding through maritime pine forest',
                        'sort_order' => 1,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Natural biological pool filtered by indigenous wetland reeds',
                        'sort_order' => 2,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Dune grass stabilization and Atlantic ocean view',
                        'sort_order' => 3,
                    ],
                    [
                        'image_path' => 'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=1200&q=80',
                        'caption' => 'Minimalist timber shaded terrace facing coastal dunes',
                        'sort_order' => 4,
                    ],
                ],
            ],
        ];

        foreach ($projects as $projectData) {
            $galleryImages = $projectData['images'] ?? [];
            unset($projectData['images']);

            $project = Project::create($projectData);

            foreach ($galleryImages as $imageData) {
                $imageData['project_id'] = $project->id;
                ProjectImage::create($imageData);
            }
        }

        // Seed initial consultation inquiries for administrative and verification testing
        Inquiry::create([
            'name' => 'Julian Finch',
            'email' => 'julian.finch@vanguard-estate.ch',
            'phone' => '+41 22 819 4022',
            'site_location' => 'Geneva, Switzerland',
            'estimated_area' => '750 m²',
            'budget_range' => '$150,000 - $300,000',
            'message' => 'We are seeking an organic landscape masterplan for our terraced lakeside residence in Cologny. We admire your stone sanctuary work and wish to integrate natural stone rills.',
            'status' => 'new',
        ]);

        Inquiry::create([
            'name' => 'Seraphina Vance',
            'email' => 'seraphina@vance-atelier.com',
            'phone' => '+1 (310) 849-2910',
            'site_location' => 'Montecito, California',
            'estimated_area' => '1,800 m²',
            'budget_range' => '$350,000+',
            'message' => 'Commissioning a comprehensive landscape overhaul for a mid-century coastal property. Looking for drought-hardy architectural specimens, rammed-earth interventions, and reflection water features.',
            'status' => 'reviewed',
        ]);
    }
}
