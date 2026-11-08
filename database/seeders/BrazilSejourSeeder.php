<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Stay;
use Illuminate\Database\Seeder;

class BrazilSejourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Avoid duplicate seeding
        if (Stay::count() > 0) {
            return;
        }

        $stays = [
            [
                'slug' => 'rio-merveilleux-baie-tropicale',
                'title' => 'Rio Merveilleux & Baie Tropicale',
                'destination' => 'Rio de Janeiro',
                'region' => 'Sud-Est',
                'duration' => '7 jours / 6 nuits',
                'days' => 7,
                'price' => 1450.00,
                'rating' => 4.90,
                'reviews_count' => 48,
                'image' => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Culture & Plage',
                'tag' => 'Coup de cœur',
                'featured' => true,
                'status' => 'active',
                'summary' => 'Découvrez la Cidade Maravilhosa, le Christ Rédempteur, le Pain de Sucre et les rythmes envoûtants de Copacabana et Ipanema.',
                'highlights' => [
                    'Visite privative du Corcovado et du Pain de Sucre',
                    'Coucher de soleil en musique à la plage d\'Ipanema',
                    'Immersion dans un atelier de samba à Santa Teresa',
                    'Croisière en goélette dans la baie de Guanabara',
                ],
                'included' => [
                    'Hôtel 4* en front de mer avec petit-déjeuner',
                    'Tous les transferts privés avec chauffeur',
                    'Guide francophone pour toutes les excursions',
                    'Billets coupe-file pour les monuments emblématiques',
                ],
                'dates' => ['15 Mars 2027', '05 Avril 2027', '18 Mai 2027'],
            ],
            [
                'slug' => 'immersion-profonde-en-amazonie',
                'title' => 'Immersion Profonde en Amazonie',
                'destination' => 'Manaus & Forêt Primaire',
                'region' => 'Nord',
                'duration' => '8 jours / 7 nuits',
                'days' => 8,
                'price' => 1890.00,
                'rating' => 4.95,
                'reviews_count' => 34,
                'image' => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Aventure & Nature',
                'tag' => 'Éco-aventure',
                'featured' => true,
                'status' => 'active',
                'summary' => 'Une expédition inoubliable au cœur du poumon vert de la planète, en écolodge flottant et nuits en pleine jungle.',
                'highlights' => [
                    'Observation de la faune nocturne (caïmans, dauphins roses)',
                    'Rencontre et partage avec une communauté caboclo',
                    'Survie douce et initiation aux plantes médicinales',
                    'Spectacle de la Rencontre des Eaux (Rio Negro & Solimões)',
                ],
                'included' => [
                    'Séjour complet en écolodge durable au cœur de la jungle',
                    'Pension complète (cuisine locale et amazonienne)',
                    'Guide naturaliste expert bilingue',
                    'Équipements d\'expédition et canoës motorisés',
                ],
                'dates' => ['10 Avril 2027', '02 Juin 2027', '15 Juillet 2027'],
            ],
            [
                'slug' => 'salvador-de-bahia-ame-afro-bresilienne',
                'title' => 'Salvador de Bahia, Âme Afro-Brésilienne',
                'destination' => 'Salvador da Bahia',
                'region' => 'Nord-Est',
                'duration' => '6 jours / 5 nuits',
                'days' => 6,
                'price' => 1320.00,
                'rating' => 4.85,
                'reviews_count' => 29,
                'image' => 'https://images.unsplash.com/photo-1544986581-efac024faf62?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Culture & Gastronomie',
                'tag' => 'Authentique',
                'featured' => true,
                'status' => 'active',
                'summary' => 'Laissez-vous envoûter par les façades colorées du Pelourinho, la capoeira, la gastronomie bahianaise et les rythmes d\'Olodum.',
                'highlights' => [
                    'Visite historique guidée du Pelourinho et de l\'église Saint-François',
                    'Atelier culinaire moqueca & dégustation d\'acarajé',
                    'Démonstration et initiation à la capoeira avec un maître local',
                    'Excursion en bateau vers l\'île de paradis d\'Itaparica',
                ],
                'included' => [
                    'Pousada de charme au cœur du quartier historique',
                    'Petits déjeuners tropicaux inclus',
                    'Visites guidées culturelles privées',
                    'Transferts aéroport aller-retour',
                ],
                'dates' => ['22 Mars 2027', '14 Mai 2027', '10 Juin 2027'],
            ],
            [
                'slug' => 'chutes-d-iguacu-safari-au-pantanal',
                'title' => 'Chutes d\'Iguaçu & Safari au Pantanal',
                'destination' => 'Foz do Iguaçu & Pantanal',
                'region' => 'Sud / Centre-Ouest',
                'duration' => '9 jours / 8 nuits',
                'days' => 9,
                'price' => 2280.00,
                'rating' => 4.98,
                'reviews_count' => 51,
                'image' => 'https://images.unsplash.com/photo-1582650625119-3a31f8418b7d?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Faune & Grands Espaces',
                'tag' => 'Spectaculaire',
                'featured' => true,
                'status' => 'active',
                'summary' => 'Les plus majestueuses cascades du monde couplées au plus vaste sanctuaire de biodiversité terrestre d\'Amérique du Sud.',
                'highlights' => [
                    'Exploration des passerelles côté brésilien et côté argentin',
                    'Survol en hélicoptère au-dessus de la Gorge du Diable',
                    'Safari photo 4x4 et bateau à la recherche du jaguar',
                    'Observation des toucans, aras et piranhas',
                ],
                'included' => [
                    'Hôtels 4* et Pousada typique dans le Pantanal',
                    'Vols intérieurs Iguaçu - Cuiabá inclus',
                    'Tous les repas en formule complète au Pantanal',
                    'Guide ornithologue et naturaliste',
                ],
                'dates' => ['08 Avril 2027', '12 Mai 2027', '20 Août 2027'],
            ],
            [
                'slug' => 'le-desert-blanc-des-lencois-maranhenses',
                'title' => 'Le Désert Blanc des Lençóis Maranhenses',
                'destination' => 'Parc National des Lençóis & São Luís',
                'region' => 'Nord-Est',
                'duration' => '7 jours / 6 nuits',
                'days' => 7,
                'price' => 1650.00,
                'rating' => 4.92,
                'reviews_count' => 22,
                'image' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Merveilles Naturelles',
                'tag' => 'Insolite',
                'featured' => false,
                'status' => 'active',
                'summary' => 'Des dunes de sable blanc immaculé ponctuées de lagunes d\'eau douce turquoise cristalline. Un décor unique au monde.',
                'highlights' => [
                    'Randonnée pieds nus au coucher du soleil sur les grandes dunes',
                    'Baignade rafraîchissante dans la Lagoa Azul et la Lagoa Bonita',
                    'Descente en bouée ou kayak du Rio Formiga',
                    'Découverte architecturale coloniale de São Luís',
                ],
                'included' => [
                    'Pousadas de charme confortables',
                    'Véhicules 4x4 tout-terrain privés pour les dunes',
                    'Guides locaux certifiés du parc',
                    'Transferts privatifs climatisés',
                ],
                'dates' => ['01 Juin 2027', '15 Juillet 2027', '10 Août 2027'],
            ],
            [
                'slug' => 'paradis-sauvage-de-fernando-de-noronha',
                'title' => 'Paradis Sauvage de Fernando de Noronha',
                'destination' => 'Archipel de Fernando de Noronha',
                'region' => 'Nord-Est',
                'duration' => '6 jours / 5 nuits',
                'days' => 6,
                'price' => 2490.00,
                'rating' => 5.00,
                'reviews_count' => 19,
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'category' => 'Luxe & Plongée',
                'tag' => 'Sanctuaire Marin',
                'featured' => false,
                'status' => 'active',
                'summary' => 'Accédez à l\'un des sanctuaires marins les plus exclusifs et préservés du monde. Requins inoffensifs, tortues géantes et dauphins.',
                'highlights' => [
                    'Snorkeling à Baía do Sancho (élue plus belle plage du monde)',
                    'Navigation matinale aux côtés de bancs de dauphins',
                    'Randonnée panoramique du Morro do Pico',
                    'Dîners gastronomiques de fruits de mer sous la voûte céleste',
                ],
                'included' => [
                    'Éco-resort d\'exception vue océan',
                    'Taxe environnementale d\'accès à l\'archipel incluse',
                    'Moniteur de plongée privé et matériel fourni',
                    'Buggy privé à disposition pendant tout le séjour',
                ],
                'dates' => ['10 Mai 2027', '04 Septembre 2027', '12 Octobre 2027'],
            ],
        ];

        $createdStays = [];
        foreach ($stays as $data) {
            $createdStays[] = Stay::create($data);
        }

        // Create sample bookings
        Booking::create([
            'reference' => 'RES-RIO892',
            'stay_id' => $createdStays[0]->id,
            'stay_title' => $createdStays[0]->title,
            'customer_name' => 'Claire & Thomas Dupont',
            'email' => 'claire.dupont@example.com',
            'phone' => '+33 6 12 34 56 78',
            'travelers' => 2,
            'travel_date' => '15 Mars 2027',
            'total_price' => 2900.00,
            'status' => 'confirmée',
            'notes' => 'Chambre avec vue mer demandée si possible.',
        ]);

        Booking::create([
            'reference' => 'RES-AMZ514',
            'stay_id' => $createdStays[1]->id,
            'stay_title' => $createdStays[1]->title,
            'customer_name' => 'Julien Lefebvre',
            'email' => 'j.lefebvre@example.com',
            'phone' => '+33 6 98 76 54 32',
            'travelers' => 1,
            'travel_date' => '10 Avril 2027',
            'total_price' => 1890.00,
            'status' => 'en_attente',
            'notes' => 'Régime végétarien pendant le séjour en écolodge.',
        ]);

        Booking::create([
            'reference' => 'RES-BAH903',
            'stay_id' => $createdStays[2]->id,
            'stay_title' => $createdStays[2]->title,
            'customer_name' => 'Famille Martin (4 pers.)',
            'email' => 'martin.voyages@example.com',
            'phone' => '+33 6 45 89 21 03',
            'travelers' => 4,
            'travel_date' => '22 Mars 2027',
            'total_price' => 5280.00,
            'status' => 'confirmée',
            'notes' => 'Deux chambres communicantes souhaitées.',
        ]);

        Booking::create([
            'reference' => 'RES-IGU421',
            'stay_id' => $createdStays[3]->id,
            'stay_title' => $createdStays[3]->title,
            'customer_name' => 'Sophie Mercier',
            'email' => 'sophie.m@example.com',
            'phone' => '+33 7 88 12 43 09',
            'travelers' => 2,
            'travel_date' => '08 Avril 2027',
            'total_price' => 4560.00,
            'status' => 'en_attente',
            'notes' => 'Intéressée par le survol hélicoptère supplémentaire.',
        ]);
    }
}

