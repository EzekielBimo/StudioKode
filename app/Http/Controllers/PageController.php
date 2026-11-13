<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display the Studio background, philosophy, team, and process.
     */
    public function studio(): Response
    {
        $studio = [
            'name' => 'StudioKode',
            'tagline' => 'Landscape Architecture & Botanical Sanctuaries',
            'manifesto' => 'We design landscapes that mediate between human dwelling and untamed nature. Our practice rejects decorative landscaping in pursuit of profound ecological coherence, spatial permanence, and visceral sensory presence.',
            'founded' => 2009,
        ];

        $philosophy = [
            [
                'number' => '01',
                'title' => 'Living Topography',
                'description' => 'We do not impose foreign forms on the land. Every terrace, retaining wall, and water rill originates from the natural inclination and geological history of the terrain.',
            ],
            [
                'number' => '02',
                'title' => 'Ecological Permanence',
                'description' => 'Our planting schemes avoid transient seasonal displays in favor of resilient climax communities, microclimate-adapted perennials, and long-lived specimen trees.',
            ],
            [
                'number' => '03',
                'title' => 'Sensory Materiality',
                'description' => 'We celebrate hand-chiseled granite, reclaimed timber, and unlacquered bronzes that absorb regional weather and acquire timeless patina.',
            ],
            [
                'number' => '04',
                'title' => 'Hydrological Stewardship',
                'description' => 'Water is treated as both an architectural mirror and a precious resource. We integrate biological filtration basins, rain gardens, and dry-creek drainage matrices.',
            ],
        ];

        $team = [
            [
                'name' => 'Kaelen Voss',
                'role' => 'Principal Landscape Architect & Founder',
                'bio' => 'Over 18 years shaping rural retreats and urban sanctuaries across Europe and East Asia. Master of Landscape Architecture, ETH Zürich.',
                'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Soraya Chen',
                'role' => 'Director of Botanical Systems & Ecology',
                'bio' => 'Botanist and horticulturist specializing in rare microclimate adaptations, moss propagation, and native alpine meadow restoration.',
                'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Mateo Aris',
                'role' => 'Spatial Design & Masterplanning Lead',
                'bio' => 'Focused on the convergence of architectural hardscaping, topographic earthmoving, and contemporary spatial flow.',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Elena Rostova',
                'role' => 'Head of Materiality & Artisan Craft',
                'bio' => 'Expert in dry-stone masonry, monolithic water features, and custom metal craft tailored for luxury private estates.',
                'image' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        $process = [
            [
                'stage' => '01',
                'title' => 'Topographical & Ecological Survey',
                'description' => 'Comprehensive geotechnical analysis, soil biology assays, solar path mapping, and native ecological cataloging of the site.',
            ],
            [
                'stage' => '02',
                'title' => 'Spatial Masterplanning & Schematics',
                'description' => 'Formulating circulation axes, outdoor rooms, viewsheds, and hydrological flows through hand sketches and digital 3D topography.',
            ],
            [
                'stage' => '03',
                'title' => 'Botanical Curation & Material Detailing',
                'description' => 'Selecting individual specimen trees, sourcing quarry-specific stone, designing bespoke water flumes, and finalizing planting palettes.',
            ],
            [
                'stage' => '04',
                'title' => 'Construction Stewardship & Cultivation',
                'description' => 'On-site direction of master stonemasons and arborists, followed by multi-year seasonal monitoring to guarantee botanical thriving.',
            ],
        ];

        $accolades = [
            ['year' => '2024', 'award' => 'Prix de Rome for Landscape Architecture — Sanctuary Category'],
            ['year' => '2023', 'award' => 'World Landscape Architecture Award — Best Private Estate'],
            ['year' => '2021', 'award' => 'European Garden Heritage Prize — Ecological Sustainability'],
        ];

        return Inertia::render('Studio/Index', [
            'studio' => $studio,
            'philosophy' => $philosophy,
            'team' => $team,
            'process' => $process,
            'accolades' => $accolades,
        ]);
    }

    /**
     * Display the Landscape and garden architectural services offered.
     */
    public function services(): Response
    {
        $services = [
            [
                'id' => 'masterplanning',
                'number' => '01',
                'title' => 'Private Estate Masterplanning',
                'tagline' => 'Comprehensive territorial vision for expansive private properties and rural estates.',
                'description' => 'We orchestrate multi-acre landholdings into cohesive ecosystems. From entrance drives and private forests to intimate courtyard gardens, we choreograph every movement and perspective across the grounds.',
                'deliverables' => [
                    'Topographic and hydrological master plan drawings',
                    '3D experiential visualization & seasonal sun studies',
                    'Grading, earthwork, and retention civil schematics',
                    'Long-term land stewardship guidelines',
                ],
            ],
            [
                'id' => 'botanical-curation',
                'number' => '02',
                'title' => 'Botanical Curation & Planting Design',
                'tagline' => 'Biodiverse, multi-tiered botanical tapestries tailored to microclimatic zones.',
                'description' => 'We curate living plant tapestries that change dynamically through the four seasons. Our selections prioritize native ecology, drought tolerance, and architectural forms that complement built volumes.',
                'deliverables' => [
                    'Botanical specification schedules & provenance tracking',
                    'Seasonal color, texture, and bloom progression charts',
                    'Soil enhancement and organic fertilization regimes',
                    'Specimen tree tagging and arborist relocation oversight',
                ],
            ],
            [
                'id' => 'water-architecture',
                'number' => '03',
                'title' => 'Water Architecture & Natural Pools',
                'tagline' => 'Chemical-free biological pools, reflecting basins, and acoustic water rills.',
                'description' => 'Water brings serenity and acoustic isolation to the garden. We design custom infinity reflection pools, stone water flumes, and natural swimming sanctuaries filtered entirely by living wetland reeds.',
                'deliverables' => [
                    'Biological filtration and reed bed hydraulic engineering',
                    'Custom monolithic stone weir and spout details',
                    'Submerged aquatic flora planting plans',
                    'Low-voltage underwater architectural illumination',
                ],
            ],
            [
                'id' => 'hardscaping',
                'number' => '04',
                'title' => 'Architectural Hardscaping & Pavilions',
                'tagline' => 'Dry-stacked regional stone, timber boardwalks, and custom outdoor pavilions.',
                'description' => 'Our hardscape structures are conceived as permanent extensions of the landscape. We work with local quarries and master stonemasons to build walls, terraces, and open-air pavilions that endure for centuries.',
                'deliverables' => [
                    'Dry-stacked and dressed stone masonry construction drawings',
                    'Timber pergola, pavilion, and bridge joinery specifications',
                    'Permeable stone paving and gravel swale grading',
                    'Integrated bronze, iron, and stone garden hardware',
                ],
            ],
            [
                'id' => 'ecological-restoration',
                'number' => '05',
                'title' => 'Ecological Restoration & Land Stewardship',
                'tagline' => 'Revitalizing degraded terrains, coastal dunes, and sub-alpine forest edges.',
                'description' => 'We remediate compromised soil structures, stabilize eroding slopes, and re-establish native flora networks that attract local wildlife and stabilize surrounding microclimates.',
                'deliverables' => [
                    'Soil biology restoration & fungal network inoculation',
                    'Bio-engineering slope stabilization schematics',
                    'Native pollinator and bird corridor establishment',
                    'Rainwater harvesting and aquifer replenishment systems',
                ],
            ],
        ];

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Display the Studio contact details and consultation inquiry form.
     */
    public function contact(): Response
    {
        $studios = [
            [
                'city' => 'Kyoto Atelier',
                'address' => 'Kamigamo Sanctuary, Kita-ku, Kyoto 603-8047, Japan',
                'email' => 'kyoto@studiokode.design',
                'phone' => '+81 75 700 8921',
                'hours' => 'Tue — Sat, 09:00 – 17:00 JST',
            ],
            [
                'city' => 'Zurich Studio',
                'address' => 'Seestrasse 142, 8002 Zürich, Switzerland',
                'email' => 'zurich@studiokode.design',
                'phone' => '+41 44 288 3310',
                'hours' => 'Mon — Fri, 08:30 – 18:00 CET',
            ],
            [
                'city' => 'Melbourne Office',
                'address' => '42 Flinders Lane, Melbourne VIC 3000, Australia',
                'email' => 'melbourne@studiokode.design',
                'phone' => '+61 3 9654 2200',
                'hours' => 'Mon — Fri, 09:00 – 17:30 AEST',
            ],
        ];

        $consultationDetails = [
            'leadTime' => 'Initial spatial masterplanning engagements typically begin 6 to 10 weeks post-commission.',
            'minimumScale' => 'We welcome private residences, country retreats, and botanical estates from 150 m² to 50+ hectares.',
        ];

        return Inertia::render('Contact/Index', [
            'studios' => $studios,
            'consultationDetails' => $consultationDetails,
        ]);
    }
}
