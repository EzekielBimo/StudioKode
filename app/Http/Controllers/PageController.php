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
            'name' => 'Kode Landscape Studio',
            'tagline' => 'Tropical Landscape Design & Build',
            'manifesto' => 'We believe in design and build as a unified process, creating landscapes that grow naturally from their environment. Rooted in the essence of place, local culture, and the people who inhabit it, our landscapes are made to belong, to endure, and to grow alongside the lives within them.',
            'founded' => 2025,
            'location' => 'Bali, Indonesia',
        ];

        $philosophy = [
            [
                'number' => '01',
                'title' => 'Unified Design & Build',
                'description' => 'We believe in design and build as a unified process, creating landscapes that grow naturally from their environment through integrated execution, sustainable practices, and locally sourced materials.',
            ],
            [
                'number' => '02',
                'title' => 'Land-First Sensitivity',
                'description' => 'Beginning each project with a deep understanding of the land; its form, climate, and natural rhythms. We do not impose design; we allow the landscape to guide the process.',
            ],
            [
                'number' => '03',
                'title' => 'Rooted in Culture & Materiality',
                'description' => 'Rooted in the essence of place, local culture, and the people who inhabit it, using natural and locally sourced materials to create functional outdoor spaces that feel alive, meaningful, and timeless.',
            ],
            [
                'number' => '04',
                'title' => 'Enduring Ecological Harmony',
                'description' => 'Every detail is crafted with care, ensuring beauty and function evolve together over time. Our landscapes are made to belong, to endure, and to grow alongside the lives within them.',
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

        $conceptSteps = [
            [
                'number' => '01',
                'title' => 'Contact Us',
                'description' => 'Clients can reach us via WhatsApp, email, or Instagram. We listen to your needs and gather initial information about the project.',
            ],
            [
                'number' => '02',
                'title' => 'Project Meeting & Site Assessment',
                'description' => 'We visit the project site to evaluate existing conditions, take precise measurements, and discuss the client’s vision, preferences, and functional requirements for the landscape design.',
            ],
            [
                'number' => '03',
                'title' => 'Moodboard & Landscape Concept',
                'description' => 'A moodboard is developed to capture the style, materials, planting approach, and overall design direction of the landscape project in line with the client’s vision.',
            ],
            [
                'number' => '04',
                'title' => 'Project Collaboration Agreement',
                'description' => 'Once the concept is approved, we finalize a design agreement covering the scope, schedule, design fee, and terms, ensuring clear understanding between our team and the client before design and execution begin.',
            ],
            [
                'number' => '05',
                'title' => 'Design & Build',
                'description' => 'Our team carries out the design and build process, including 3D renderings, technical plans, planting schemes, and site execution, bringing the concept to life.',
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
            'conceptSteps' => $conceptSteps,
            'accolades' => $accolades,
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
