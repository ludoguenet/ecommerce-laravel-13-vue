<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\Tag;
use Lunar\Models\TaxClass;

class CoffeeProductSeeder extends Seeder
{
    public function run(): void
    {
        $taxClass = TaxClass::first();
        $currency = Currency::where('default', true)->first();
        $productType = ProductType::first();

        $brands = $this->createBrands();
        $tags = $this->createTags();
        $this->createProducts($brands, $tags, $taxClass->id, $currency->id, $productType->id);
    }

    /**
     * @return array<string, Brand>
     */
    private function createBrands(): array
    {
        $names = ['Arborealis Roasters', 'Café du Monde', 'Black Peak Roasters'];

        return collect($names)
            ->mapWithKeys(fn (string $name) => [
                $name => Brand::firstOrCreate(['name' => $name]),
            ])
            ->all();
    }

    /**
     * @return array<string, Tag>
     */
    private function createTags(): array
    {
        $values = [
            'ARABICA', 'FLORAL', 'FRUITÉ', 'BLEND', 'ÉPICÉ', 'SINGLE-ORIGIN',
            'ROBUSTA', 'CHOCOLAT', 'MOULU', 'CAPSULE', 'BIO', 'CARAMEL',
        ];

        return collect($values)
            ->mapWithKeys(fn (string $value) => [
                $value => Tag::firstOrCreate(['value' => $value]),
            ])
            ->all();
    }

    /**
     * All arabica single-origin entries (shared across multiple collections).
     *
     * @return array<int, array{name: string, code: string, notes: string, price: int, tags: string[]}>
     */
    private function arabicaOrigins(): array
    {
        return [
            ['name' => 'Guatemala Antigua',      'code' => 'GUA', 'notes' => 'Chocolat noir, épices douces',         'price' => 1490, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Brésil Santos',           'code' => 'BRA', 'notes' => 'Noisette, caramel léger, corps doux',  'price' => 1190, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'Panama Geisha',           'code' => 'PAN', 'notes' => 'Jasmin, pêche, bergamote',             'price' => 2890, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Pérou Cajamarca',         'code' => 'PER', 'notes' => 'Chocolat au lait, amande douce',       'price' => 1350, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Mexique Chiapas',         'code' => 'MEX', 'notes' => 'Noix, cacao, légèrement sucré',        'price' => 1290, 'tags' => ['ARABICA']],
            ['name' => 'Honduras Santa Barbara',  'code' => 'HON', 'notes' => 'Miel, agrumes, finale fruitée',        'price' => 1300, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Rwanda Bourbon',          'code' => 'RWA', 'notes' => 'Cassis, hibiscus, acidité vive',       'price' => 1550, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Éthiopie Sidamo',         'code' => 'SID', 'notes' => 'Jasmin, citron vert, thé vert',        'price' => 1650, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Éthiopie Guji',           'code' => 'GUJ', 'notes' => 'Myrtille, mûre, fleurs blanches',      'price' => 1750, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Éthiopie Kaffa',          'code' => 'KAF', 'notes' => 'Prune, abricot, notes épicées',        'price' => 1690, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Éthiopie Limu',           'code' => 'LIM', 'notes' => 'Pamplemoussier, noisette grillée',     'price' => 1600, 'tags' => ['ARABICA']],
            ['name' => 'Éthiopie Djimmah',        'code' => 'DJI', 'notes' => 'Épices, cannelle, corps généreux',     'price' => 1450, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Colombie Nariño',         'code' => 'NAR', 'notes' => 'Pomme verte, caramel brun, acidité',   'price' => 1390, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Colombie Tolima',         'code' => 'TOL', 'notes' => 'Pêche blanche, floral, doux',          'price' => 1420, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Colombie Cauca',          'code' => 'CAU', 'notes' => 'Agrumes, miel, caramel léger',         'price' => 1380, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'Colombie Antioquia',      'code' => 'ANT', 'notes' => 'Chocolat noir, noix de cajou',         'price' => 1350, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Colombie Sierra Nevada',  'code' => 'SIE', 'notes' => 'Vanille, raisin, onctueux',            'price' => 1480, 'tags' => ['ARABICA']],
            ['name' => 'Brésil Cerrado',          'code' => 'CER', 'notes' => 'Caramel, chocolat au lait, doux',      'price' => 1150, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'Brésil Sul de Minas',     'code' => 'SUL', 'notes' => 'Noix, sucre roux, corps plein',        'price' => 1200, 'tags' => ['ARABICA']],
            ['name' => 'Brésil Alta Mogiana',     'code' => 'MOG', 'notes' => 'Chocolat, épices douces, équilibré',   'price' => 1220, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Guatemala Huehuetenango', 'code' => 'HUE', 'notes' => 'Pomme, miel, acidité franche',         'price' => 1420, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Guatemala Cobán',         'code' => 'COB', 'notes' => 'Chocolat, épices, corps moyen',        'price' => 1380, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Guatemala Acatenango',    'code' => 'ACA', 'notes' => 'Caramel, fruit rouge, délicat',        'price' => 1450, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Kenya AB',                'code' => 'KAB', 'notes' => 'Tomate, groseille, acidité vive',      'price' => 1590, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Kenya Kiambu',            'code' => 'KIA', 'notes' => 'Fruit de la passion, agrumes',         'price' => 1650, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Kenya Kirinyaga',         'code' => 'KIR', 'notes' => 'Cassis, framboise, longue finale',     'price' => 1680, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Costa Rica Tarrazú',      'code' => 'TAR', 'notes' => 'Noisette, agrumes, corps équilibré',   'price' => 1340, 'tags' => ['ARABICA']],
            ['name' => 'Costa Rica Tres Ríos',    'code' => 'TRE', 'notes' => 'Floral, bergamote, douceur subtile',   'price' => 1390, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Nicaragua Jinotega',      'code' => 'JIN', 'notes' => 'Chocolat noir, noix, doux',            'price' => 1290, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Nicaragua Matagalpa',     'code' => 'MAT', 'notes' => 'Caramel, sucre brun, corps moyen',     'price' => 1270, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'El Salvador Santa Ana',   'code' => 'SAN', 'notes' => 'Sucre de canne, fruit, douceur',       'price' => 1310, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'El Salvador Pacamara',    'code' => 'PAC', 'notes' => 'Jasmin, pêche, bergamote',             'price' => 1580, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Bolivie Caranavi',        'code' => 'BOL', 'notes' => 'Fraise, mandarine, corps léger',       'price' => 1450, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Malawi Mzuzu',            'code' => 'MAL', 'notes' => 'Agrumes, cassis, acidité fine',        'price' => 1520, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Zambie Isanya',           'code' => 'ZAM', 'notes' => 'Noix, noisette, légèrement fruité',    'price' => 1490, 'tags' => ['ARABICA']],
            ['name' => 'Chine Yunnan',            'code' => 'YUN', 'notes' => 'Chocolat noir, épices, corps plein',   'price' => 1320, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Myanmar Shan',            'code' => 'MYA', 'notes' => 'Floral, agrumes, corps léger',         'price' => 1550, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Burundi Ngozi',           'code' => 'BUR', 'notes' => 'Abricot, jasmin, thé noir',            'price' => 1600, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Inde Monsoon Malabar',    'code' => 'IND', 'notes' => 'Bois, épices, corps généreux',         'price' => 1380, 'tags' => ['ARABICA', 'ÉPICÉ']],
        ];
    }

    /**
     * Specialty single-origin coffees for "Cafés d'origine".
     *
     * @return array<int, array{name: string, code: string, notes: string, price: int, tags: string[]}>
     */
    private function specialtyOrigins(): array
    {
        return [
            ['name' => 'Tanzanie Peaberry',             'code' => 'TAN',  'notes' => 'Ananas, caramel, corps soyeux',          'price' => 1690, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Yémen Moka',                    'code' => 'YEM',  'notes' => 'Épices, fruit sec, notes vinées',         'price' => 2250, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Éthiopie Harrar',               'code' => 'ETH2', 'notes' => 'Vin rouge, bleuet, notes épicées',        'price' => 1790, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Équateur Pichincha',            'code' => 'EQU',  'notes' => 'Prune, vanille, douceur persistante',     'price' => 1850, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Jamaïque Blue Mountain',        'code' => 'JAM',  'notes' => 'Délicat, floral, légèrement sucré',       'price' => 3500, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Hawaï Kona',                    'code' => 'HAW',  'notes' => 'Noix de macadamia, miel, corps soyeux',   'price' => 4200, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Hawaï Maui',                    'code' => 'MAU',  'notes' => 'Floral, ananas, acidité subtile',         'price' => 3800, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Yémen Haraaz',                  'code' => 'HAR',  'notes' => 'Tamarind, épices, vin rouge',             'price' => 2800, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Yémen Bani Matar',              'code' => 'BNM',  'notes' => 'Chocolat, épices orientales, profond',    'price' => 2600, 'tags' => ['ARABICA', 'ÉPICÉ', 'CHOCOLAT']],
            ['name' => 'Équateur Galápagos',            'code' => 'GAL',  'notes' => 'Citron, miel, corps léger',               'price' => 2200, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Équateur Loja',                 'code' => 'LOJ',  'notes' => 'Framboise, sucre brun, floral',           'price' => 1950, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Indonésie Java Preanger',       'code' => 'JAV',  'notes' => 'Terreux, épices, corps plein',            'price' => 1680, 'tags' => ['SINGLE-ORIGIN', 'ÉPICÉ']],
            ['name' => 'Indonésie Sulawesi Toraja',     'code' => 'SUL',  'notes' => 'Cèdre, réglisse, chocolat noir',          'price' => 1750, 'tags' => ['SINGLE-ORIGIN', 'CHOCOLAT']],
            ['name' => 'Indonésie Flores Bajawa',       'code' => 'FLO',  'notes' => 'Chocolat, caramel, acidité fraîche',      'price' => 1720, 'tags' => ['SINGLE-ORIGIN', 'CARAMEL']],
            ['name' => 'Indonésie Bali Kintamani',      'code' => 'BAL',  'notes' => 'Citron, noisette, corps léger',           'price' => 1690, 'tags' => ['SINGLE-ORIGIN', 'FRUITÉ']],
            ['name' => 'Timor-Leste Ermera',            'code' => 'TIM',  'notes' => 'Noix, caramel, corps généreux',           'price' => 1580, 'tags' => ['SINGLE-ORIGIN', 'CARAMEL']],
            ['name' => 'Papouasie-Nv-Guinée Sigri',     'code' => 'PNG',  'notes' => 'Chocolat, floral, corps délicat',         'price' => 1850, 'tags' => ['SINGLE-ORIGIN', 'FLORAL']],
            ['name' => 'Éthiopie Natural',              'code' => 'ENT',  'notes' => 'Fraise, myrtille, vin rouge',             'price' => 1890, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Éthiopie Washed',               'code' => 'EWS',  'notes' => 'Jasmin, citrus, propre et vif',           'price' => 1790, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Colombie Pink Bourbon',         'code' => 'CPB',  'notes' => 'Rose, fraise, bergamote',                 'price' => 2100, 'tags' => ['ARABICA', 'FLORAL', 'FRUITÉ']],
            ['name' => 'Colombie Geisha',               'code' => 'CGS',  'notes' => 'Jasmin, kiwi, pêche de vigne',            'price' => 2800, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Kenya SL28',                    'code' => 'KSL',  'notes' => 'Groseille, tomate, acidité vive',         'price' => 1980, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Panama Caturra',                'code' => 'PCA',  'notes' => 'Agrumes, miel, noisette',                 'price' => 1850, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Haïti Bleu du Nord',            'code' => 'HAI',  'notes' => 'Cacao, caramel, léger acidité',           'price' => 2200, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'Réunion Bourbon Pointu',        'code' => 'REU',  'notes' => 'Floral, sucré, très rare',                'price' => 4800, 'tags' => ['ARABICA', 'SINGLE-ORIGIN', 'FLORAL']],
            ['name' => 'Cuba Crystal Mountain',         'code' => 'CUB',  'notes' => 'Noisette, chocolat, corps doux',          'price' => 2400, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Éthiopie Bale Mountain',        'code' => 'EBM',  'notes' => 'Épices sauvages, fruit exotique',         'price' => 1950, 'tags' => ['ARABICA', 'ÉPICÉ']],
            ['name' => 'Éthiopie Nekisse',              'code' => 'ENK',  'notes' => 'Framboise, melon, fleurs blanches',       'price' => 2100, 'tags' => ['ARABICA', 'FRUITÉ', 'FLORAL']],
            ['name' => 'Sumatra Mandheling',            'code' => 'SMN',  'notes' => 'Terreux, épices, corps intense',          'price' => 1680, 'tags' => ['SINGLE-ORIGIN', 'ÉPICÉ']],
            ['name' => 'Sumatra Lintong',               'code' => 'SLN',  'notes' => 'Herbes, chocolat noir, bois',             'price' => 1720, 'tags' => ['SINGLE-ORIGIN', 'CHOCOLAT']],
            ['name' => 'Java Ijen',                     'code' => 'IJE',  'notes' => 'Fumé, épices, chocolat amer',             'price' => 1760, 'tags' => ['SINGLE-ORIGIN', 'ÉPICÉ']],
            ['name' => 'Flores Sumbawa',                'code' => 'SBW',  'notes' => 'Chocolat, fruits noirs, terreux',         'price' => 1690, 'tags' => ['SINGLE-ORIGIN', 'CHOCOLAT']],
            ['name' => 'Nicaragua Segovia',             'code' => 'NSG',  'notes' => 'Chocolat noir, caramel brun, équilibré',  'price' => 1340, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Costa Rica Natural',            'code' => 'CRN',  'notes' => 'Cerise, vanille, fraise confite',         'price' => 1590, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Timor-Leste Maubesse',          'code' => 'TLM',  'notes' => 'Épices, bois, notes de cacao',            'price' => 1620, 'tags' => ['SINGLE-ORIGIN', 'ÉPICÉ']],
            ['name' => 'Papouasie Wahgi Valley',        'code' => 'PNG2', 'notes' => 'Fruité, chocolat, terreux léger',         'price' => 1790, 'tags' => ['SINGLE-ORIGIN', 'FRUITÉ']],
            ['name' => 'Éthiopie Shakiso',              'code' => 'ESH',  'notes' => 'Pamplemousse, jasmin, fruit exotique',    'price' => 1980, 'tags' => ['ARABICA', 'FLORAL', 'FRUITÉ']],
            ['name' => 'El Salvador Bourbon',           'code' => 'ESB',  'notes' => 'Caramel, noisette, chocolat doux',        'price' => 1420, 'tags' => ['ARABICA', 'CARAMEL']],
            ['name' => 'Sulawesi Kalossi',              'code' => 'SKL',  'notes' => 'Réglisse, cannelle, corps intense',       'price' => 1740, 'tags' => ['SINGLE-ORIGIN', 'ÉPICÉ']],
            ['name' => 'Bali Munduk',                   'code' => 'BMU',  'notes' => 'Chocolat blanc, cannelle, doux',          'price' => 1680, 'tags' => ['SINGLE-ORIGIN', 'CARAMEL']],
            ['name' => 'Kenya Nyeri',                   'code' => 'KNY',  'notes' => 'Mûre, cerise, acidité brillante',         'price' => 1820, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Guatemala San Marcos',          'code' => 'GSM',  'notes' => 'Fleurs, agrumes, corps léger',            'price' => 1490, 'tags' => ['ARABICA', 'FLORAL']],
            ['name' => 'Honduras Intibucá',             'code' => 'HNI',  'notes' => 'Pomme rouge, caramel, douceur',           'price' => 1360, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Rwanda Nyamasheke',             'code' => 'RNS',  'notes' => 'Hibiscus, cerise, agrumes',               'price' => 1620, 'tags' => ['ARABICA', 'FRUITÉ']],
            ['name' => 'Pérou Cusco',                   'code' => 'PCU',  'notes' => 'Chocolat, vanille, corps soyeux',         'price' => 1410, 'tags' => ['ARABICA', 'CHOCOLAT']],
            ['name' => 'Pérou Amazonas',                'code' => 'PAZ',  'notes' => 'Floral, caramel, notes lactées',          'price' => 1380, 'tags' => ['ARABICA', 'FLORAL']],
        ];
    }

    /**
     * Robusta single origins.
     *
     * @return array<int, array{name: string, code: string, notes: string, price: int, tags: string[]}>
     */
    private function robustaOrigins(): array
    {
        return [
            ['name' => 'Vietnam Cà Phê',             'code' => 'VIE',  'notes' => 'Chocolat intense, terre humide',       'price' => 1090, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Ouganda Mt Elgon',            'code' => 'OUG',  'notes' => 'Bois, chocolat amer, corps puissant', 'price' => 1150, 'tags' => ['ROBUSTA']],
            ['name' => 'Indonésie Sumatra',           'code' => 'SUM',  'notes' => 'Terreux, bois de cèdre, plein corps', 'price' => 1200, 'tags' => ['ROBUSTA']],
            ['name' => 'Cameroun Robusta',            'code' => 'CAM',  'notes' => 'Épices, cacao brut, intense',         'price' => 1050, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Vietnam Đắk Lắk',            'code' => 'DAL',  'notes' => 'Café noir intense, tabac doux',       'price' => 1080, 'tags' => ['ROBUSTA']],
            ['name' => 'Vietnam Lâm Đồng',            'code' => 'LAM',  'notes' => 'Cacao, légèrement sucré, corps plein', 'price' => 1100, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Vietnam Gia Lai',             'code' => 'GIA',  'notes' => 'Terreux, notes boisées, intense',     'price' => 1070, 'tags' => ['ROBUSTA']],
            ['name' => 'Vietnam Buôn Ma Thuột',       'code' => 'BMT',  'notes' => 'Chocolat amer, cacao, puissant',      'price' => 1090, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Vietnam Cầu Đất',             'code' => 'CAD',  'notes' => 'Arômes floraux, moins amer',          'price' => 1150, 'tags' => ['ROBUSTA', 'FLORAL']],
            ['name' => 'Indonésie Java Robusta',      'code' => 'IJR',  'notes' => 'Épicé, terreux, corps intense',       'price' => 1180, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Indonésie Flores Robusta',    'code' => 'FLR',  'notes' => 'Cèdre, herbes, épices sèches',       'price' => 1190, 'tags' => ['ROBUSTA']],
            ['name' => 'Ouganda Bugisu',              'code' => 'BUG',  'notes' => 'Cacao, fruits noirs, acidité',        'price' => 1130, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Ouganda Rwenzori',            'code' => 'RWZ',  'notes' => 'Bois, noisette, corps plein',         'price' => 1110, 'tags' => ['ROBUSTA']],
            ['name' => 'Côte d\'Ivoire Aboisso',      'code' => 'ABO',  'notes' => 'Chocolat noir, torréfié, terreux',    'price' => 1000, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Côte d\'Ivoire Abengourou',   'code' => 'ABG',  'notes' => 'Cacao brut, épices, corps intense',   'price' => 990,  'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Cameroun Bafoussam',          'code' => 'BAF',  'notes' => 'Épices, bois, légèrement amer',       'price' => 1040, 'tags' => ['ROBUSTA']],
            ['name' => 'Cameroun Bertoua',            'code' => 'BER',  'notes' => 'Cacao sombre, terreux, robuste',      'price' => 1020, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Philippines Benguet',         'code' => 'BNG',  'notes' => 'Chocolat, noisette, corps moyen',     'price' => 1180, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Philippines Sagada',          'code' => 'SAG',  'notes' => 'Fruité, légèrement acide, fin',       'price' => 1200, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Philippines Bukidnon',        'code' => 'BUK',  'notes' => 'Épicé, cacao, corps puissant',        'price' => 1160, 'tags' => ['ROBUSTA']],
            ['name' => 'Inde Chikmagalur',            'code' => 'CHK',  'notes' => 'Épices, bois de santal, doux',        'price' => 1140, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Inde Coorg',                  'code' => 'COO',  'notes' => 'Chocolat, caramel, corps généreux',   'price' => 1130, 'tags' => ['ROBUSTA', 'CARAMEL']],
            ['name' => 'Congo Kivu',                  'code' => 'KIV',  'notes' => 'Cacao, fruits noirs, acidité',        'price' => 1160, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Madagascar Robusta',          'code' => 'MAD',  'notes' => 'Fruité, cacao, exotique',             'price' => 1200, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Togo Robusta',                'code' => 'TOG',  'notes' => 'Terreux, épices, corps plein',        'price' => 990,  'tags' => ['ROBUSTA']],
            ['name' => 'Angola Robusta',              'code' => 'ANG',  'notes' => 'Bois, cacao, légèrement amer',        'price' => 1010, 'tags' => ['ROBUSTA']],
            ['name' => 'Sierra Leone Robusta',        'code' => 'SLE',  'notes' => 'Cacao sombre, épices, intense',       'price' => 980,  'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Tanzanie Robusta',            'code' => 'TAR',  'notes' => 'Fruits noirs, bois, corps moyen',     'price' => 1050, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Brésil Conillon',             'code' => 'CON',  'notes' => 'Chocolat amer, cacao, intense',       'price' => 1020, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Brésil Espírito Santo',       'code' => 'ESP',  'notes' => 'Terreux, épices, corps puissant',     'price' => 1000, 'tags' => ['ROBUSTA']],
            ['name' => 'Équateur Robusta',            'code' => 'EQR',  'notes' => 'Floral, cacao, légèrement acide',     'price' => 1080, 'tags' => ['ROBUSTA', 'FLORAL']],
            ['name' => 'Indonésie Sulawesi Robusta',  'code' => 'ISR',  'notes' => 'Réglisse, épices, corps intense',     'price' => 1180, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Vietnam Langbian',            'code' => 'LBN',  'notes' => 'Notes florales, terreux, aromatique', 'price' => 1140, 'tags' => ['ROBUSTA', 'FLORAL']],
            ['name' => 'Gabon Robusta',               'code' => 'GAB',  'notes' => 'Bois, épices, amer puissant',         'price' => 1000, 'tags' => ['ROBUSTA']],
            ['name' => 'Ghana Robusta',               'code' => 'GHA',  'notes' => 'Cacao, chocolat noir, terreux',       'price' => 990,  'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Guinée Robusta',              'code' => 'GUI',  'notes' => 'Boisé, épicé, corps généreux',        'price' => 970,  'tags' => ['ROBUSTA']],
            ['name' => 'Liberia Robusta',             'code' => 'LIB',  'notes' => 'Fruits exotiques, bois, rare',        'price' => 1100, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Colombie Robusta',            'code' => 'COR',  'notes' => 'Cacao, herbes, corps puissant',       'price' => 1050, 'tags' => ['ROBUSTA']],
            ['name' => 'Cuba Robusta',                'code' => 'CUR',  'notes' => 'Épices, bois, intensité maîtrisée',   'price' => 1200, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Pérou Robusta',               'code' => 'PER2', 'notes' => 'Cacao, fruits secs, corps généreux', 'price' => 1030, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Indonésie Bali Robusta',      'code' => 'IBR',  'notes' => 'Épicé, boisé, notes de réglisse',    'price' => 1160, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
            ['name' => 'Timor-Leste Robusta',         'code' => 'TLR',  'notes' => 'Herbacé, bois, intense',             'price' => 1090, 'tags' => ['ROBUSTA']],
            ['name' => 'Kenya Robusta',               'code' => 'KER',  'notes' => 'Fruits noirs, boisé, corps plein',   'price' => 1080, 'tags' => ['ROBUSTA', 'FRUITÉ']],
            ['name' => 'Éthiopie Robusta',            'code' => 'ETR',  'notes' => 'Floral, terreux, arômes complexes',  'price' => 1120, 'tags' => ['ROBUSTA', 'FLORAL']],
            ['name' => 'Vietnam Đắk Nông',            'code' => 'DKN',  'notes' => 'Chocolat, épices, corps massif',     'price' => 1060, 'tags' => ['ROBUSTA', 'CHOCOLAT']],
            ['name' => 'Inde Mysore Robusta',          'code' => 'MYS',  'notes' => 'Épices, bois de santal, terreux',     'price' => 1120, 'tags' => ['ROBUSTA', 'ÉPICÉ']],
        ];
    }

    /**
     * Blend definitions.
     *
     * @return array<int, array{name: string, code: string, notes: string, price: int, tags: string[]}>
     */
    private function blends(): array
    {
        return [
            ['name' => 'Espresso Classico',       'code' => 'ESP',  'notes' => 'Noisette, chocolat, caramel',               'price' => 1390, 'tags' => ['BLEND']],
            ['name' => 'Morning Blend',            'code' => 'MOR',  'notes' => 'Agrumes, floral, légèreté en tasse',        'price' => 1150, 'tags' => ['BLEND', 'FLORAL']],
            ['name' => 'Dark Roast Intense',       'code' => 'DRK',  'notes' => 'Fumé, réglisse, chocolat noir profond',     'price' => 1350, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Breakfast Blend',          'code' => 'BRK',  'notes' => 'Équilibré, doux, idéal pour le matin',      'price' => 1090, 'tags' => ['BLEND']],
            ['name' => 'Barista Signature',        'code' => 'BAR',  'notes' => 'Caramel, amande, vanille subtile',          'price' => 1590, 'tags' => ['BLEND']],
            ['name' => 'Espresso Romano',          'code' => 'ROM',  'notes' => 'Épices, cacao, rondeur méditerranéenne',    'price' => 1450, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Espresso Napolitano',      'code' => 'NAP',  'notes' => 'Fruits secs, chocolat, corps intense',      'price' => 1420, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Espresso Siciliano',       'code' => 'SIC',  'notes' => 'Amande, agrumes, corps équilibré',          'price' => 1380, 'tags' => ['BLEND']],
            ['name' => 'Espresso Veneziano',       'code' => 'VEN',  'notes' => 'Vanille, caramel, crème onctueuse',         'price' => 1460, 'tags' => ['BLEND', 'CARAMEL']],
            ['name' => 'Espresso Fiorentino',      'code' => 'FIO',  'notes' => 'Floral, léger, notes de miel',             'price' => 1400, 'tags' => ['BLEND', 'FLORAL']],
            ['name' => 'Espresso Milanese',        'code' => 'MIL',  'notes' => 'Chocolat doux, noisette, corpo pieno',     'price' => 1390, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Sunrise Blend',            'code' => 'SUR',  'notes' => 'Agrumes, miel, légèreté florale',          'price' => 1180, 'tags' => ['BLEND', 'FLORAL']],
            ['name' => 'Evening Blend',            'code' => 'EVE',  'notes' => 'Chocolat noir, réglisse, corps plein',     'price' => 1250, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Midnight Roast',           'code' => 'MID',  'notes' => 'Fumé, épices, très corsé',                 'price' => 1320, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Maison Signature',         'code' => 'MAI',  'notes' => 'Caramel, noisette, corps soyeux',          'price' => 1490, 'tags' => ['BLEND', 'CARAMEL']],
            ['name' => 'Blend Prestige',           'code' => 'PRE',  'notes' => 'Chocolat belge, vanille, raffiné',         'price' => 1650, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Blend Excellence',         'code' => 'EXC',  'notes' => 'Grand cru, notes complexes, lingering',    'price' => 1780, 'tags' => ['BLEND']],
            ['name' => 'Réserve du Patron',        'code' => 'RES',  'notes' => 'Sélection du maître torréfacteur',         'price' => 1850, 'tags' => ['BLEND']],
            ['name' => 'Saison Automne',           'code' => 'AUT',  'notes' => 'Épices d\'automne, caramel, fruits secs',  'price' => 1380, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Saison Hiver',             'code' => 'HIV',  'notes' => 'Chocolat chaud, cannelle, chaleur',        'price' => 1390, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Saison Printemps',         'code' => 'PRI',  'notes' => 'Floral, léger, notes de cerise',           'price' => 1360, 'tags' => ['BLEND', 'FLORAL']],
            ['name' => 'Saison Été',               'code' => 'ETE',  'notes' => 'Fruité, agrumes, corps rafraîchissant',    'price' => 1340, 'tags' => ['BLEND', 'FRUITÉ']],
            ['name' => 'House Blend',              'code' => 'HOU',  'notes' => 'Équilibré, accessible, idéal quotidien',   'price' => 1090, 'tags' => ['BLEND']],
            ['name' => 'Premium Blend',            'code' => 'PMB',  'notes' => 'Sélection soignée, arômes développés',     'price' => 1280, 'tags' => ['BLEND']],
            ['name' => 'Grand Cru Blend',          'code' => 'GCB',  'notes' => 'Meilleurs lots, torréfaction précise',     'price' => 1680, 'tags' => ['BLEND']],
            ['name' => 'Méditerranée',             'code' => 'MED',  'notes' => 'Épices du soleil, corps ensoleillé',       'price' => 1320, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Horizon',                  'code' => 'HOR',  'notes' => 'Notes exotiques, finesse et caractère',    'price' => 1400, 'tags' => ['BLEND']],
            ['name' => 'Doux Réveil',              'code' => 'DRV',  'notes' => 'Doux, sucré, parfait sans sucre',         'price' => 1150, 'tags' => ['BLEND']],
            ['name' => 'Corsé Extrême',            'code' => 'CEX',  'notes' => 'Extra fort, expresso puissant',            'price' => 1290, 'tags' => ['BLEND']],
            ['name' => 'Équilibré Parfait',        'code' => 'EQP',  'notes' => 'Acidité, corps et douceur en harmonie',   'price' => 1230, 'tags' => ['BLEND']],
            ['name' => 'Blend Bio',                'code' => 'BBB',  'notes' => 'Agriculture biologique, doux et pur',      'price' => 1490, 'tags' => ['BLEND', 'BIO']],
            ['name' => 'Blend Équitable',          'code' => 'BEQ',  'notes' => 'Commerce équitable, conscience et goût',   'price' => 1380, 'tags' => ['BLEND', 'BIO']],
            ['name' => 'Espresso Torinese',        'code' => 'TOR',  'notes' => 'Caramel, chocolat doux, velours en tasse', 'price' => 1410, 'tags' => ['BLEND', 'CARAMEL']],
            ['name' => 'Blend Latitude',           'code' => 'LAT',  'notes' => 'Trois continents, un caractère unique',    'price' => 1520, 'tags' => ['BLEND']],
            ['name' => 'Crépuscule',               'code' => 'CRE',  'notes' => 'Fumé léger, bois, sombre et profond',      'price' => 1360, 'tags' => ['BLEND']],
            ['name' => 'Dawn Blend',               'code' => 'DWN',  'notes' => 'Citrus, floral, éveille les sens',         'price' => 1180, 'tags' => ['BLEND', 'FLORAL']],
            ['name' => 'Espresso Genovese',        'code' => 'GEN',  'notes' => 'Noisette, amande, notes marines',          'price' => 1360, 'tags' => ['BLEND']],
            ['name' => 'Blend Afrique',            'code' => 'AFR',  'notes' => 'Fruité d\'Afrique, acidité et caractère',  'price' => 1420, 'tags' => ['BLEND', 'FRUITÉ']],
            ['name' => 'Blend Asie',               'code' => 'ASI',  'notes' => 'Terreux d\'Asie, épices et profondeur',    'price' => 1380, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Blend Amérique',           'code' => 'AME',  'notes' => 'Caramel des Amériques, équilibré',         'price' => 1290, 'tags' => ['BLEND', 'CARAMEL']],
            ['name' => 'Nuit Étoilée',             'code' => 'NUI',  'notes' => 'Profond, boisé, chocolat noir',            'price' => 1410, 'tags' => ['BLEND', 'CHOCOLAT']],
            ['name' => 'Premier Réveil',           'code' => 'PMR',  'notes' => 'Doux, caramel, corps léger et accueillant', 'price' => 1130, 'tags' => ['BLEND', 'CARAMEL']],
            ['name' => 'Espresso Bolognese',       'code' => 'BOL',  'notes' => 'Chocolat noir, épices, corps imposant',    'price' => 1440, 'tags' => ['BLEND', 'ÉPICÉ']],
            ['name' => 'Barista Reserve',          'code' => 'BRV',  'notes' => 'Cuvée du barista, notes complexes',        'price' => 1720, 'tags' => ['BLEND']],
            ['name' => 'Blend Privilège',          'code' => 'BPR',  'notes' => 'Lots sélectionnés, expérience raffinée',   'price' => 1780, 'tags' => ['BLEND']],
            ['name' => 'Blend Élite',              'code' => 'BEL',  'notes' => 'Meilleure sélection de la saison',         'price' => 1860, 'tags' => ['BLEND']],
        ];
    }

    /**
     * @param  array<string, Brand>  $brands
     * @param  array<string, Tag>  $tags
     */
    private function createProducts(array $brands, array $tags, int $taxClassId, int $currencyId, int $productTypeId): void
    {
        $arborealis = $brands['Arborealis Roasters'];
        $cafeDuMonde = $brands['Café du Monde'];
        $blackPeak = $brands['Black Peak Roasters'];

        $arabica = $this->arabicaOrigins();
        $specialty = $this->specialtyOrigins();
        $robusta = $this->robustaOrigins();
        $blendList = $this->blends();

        $collectionCache = [];

        $batches = [
            // --- Arabica pur ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'],
                'description' => '<p>'.$o['notes'].' — 250g</p>',
                'sku' => 'ARB-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 8 + ($i % 40),
                'price' => $o['price'],
                'collection' => 'Arabica pur',
                'brand' => $arborealis,
                'tags' => $o['tags'],
            ], $arabica, array_keys($arabica)),

            // --- Cafés d'origine ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'],
                'description' => '<p>'.$o['notes'].' — 250g</p>',
                'sku' => 'ORI-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 4 + ($i % 30),
                'price' => $o['price'],
                'collection' => "Cafés d'origine",
                'brand' => $arborealis,
                'tags' => $o['tags'],
            ], $specialty, array_keys($specialty)),

            // --- Robusta pur ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'],
                'description' => '<p>'.$o['notes'].' — 250g</p>',
                'sku' => 'ROB-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 6 + ($i % 35),
                'price' => $o['price'],
                'collection' => 'Robusta pur',
                'brand' => $blackPeak,
                'tags' => $o['tags'],
            ], $robusta, array_keys($robusta)),

            // --- Mélanges (blends) ---
            ...array_map(fn ($b, $i) => [
                'name' => $b['name'],
                'description' => '<p>'.$b['notes'].' — 250g</p>',
                'sku' => 'BLD-'.$b['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 15 + ($i % 45),
                'price' => $b['price'],
                'collection' => 'Mélanges (blends)',
                'brand' => ($i % 3 === 2) ? $blackPeak : $cafeDuMonde,
                'tags' => $b['tags'],
            ], $blendList, array_keys($blendList)),

            // --- Cafés en grains (whole bean — use arabica + specialty origins) ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'].' — Grains',
                'description' => '<p>'.$o['notes'].', en grains entiers — 250g</p>',
                'sku' => 'GRN-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 10 + ($i % 40),
                'price' => $o['price'] + 50,
                'collection' => 'Cafés en grains',
                'brand' => ($i % 2 === 0) ? $arborealis : $cafeDuMonde,
                'tags' => array_unique([...$o['tags'], 'ARABICA']),
            ], array_merge($arabica, $specialty), array_keys(array_merge($arabica, $specialty))),

            // --- Café moulus (ground — parent collection, blends + top arabica) ---
            ...array_map(fn ($b, $i) => [
                'name' => $b['name'].' Moulu',
                'description' => '<p>'.$b['notes'].', café moulu prêt à l\'emploi — 250g</p>',
                'sku' => 'CAF-'.$b['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 12 + ($i % 40),
                'price' => $b['price'],
                'collection' => 'Café moulus',
                'brand' => $cafeDuMonde,
                'tags' => [...$b['tags'], 'MOULU'],
            ], array_merge($blendList, array_slice($arabica, 0, 6)), array_keys(array_merge($blendList, array_slice($arabica, 0, 6)))),

            // --- Mouture fine / espresso (arabica + top specialty) ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'].' Espresso',
                'description' => '<p>'.$o['notes'].', mouture fine pour espresso — 250g</p>',
                'sku' => 'MFN-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 10 + ($i % 35),
                'price' => $o['price'],
                'collection' => 'Mouture fine (espresso)',
                'brand' => ($i % 2 === 0) ? $arborealis : $cafeDuMonde,
                'tags' => [...$o['tags'], 'MOULU'],
            ], array_merge($arabica, array_slice($specialty, 0, 12)), array_keys(array_merge($arabica, array_slice($specialty, 0, 12)))),

            // --- Mouture moyenne / filtre (specialty + top arabica) ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'].' Filtre',
                'description' => '<p>'.$o['notes'].', mouture moyenne pour cafetière filtre — 250g</p>',
                'sku' => 'MMY-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 12 + ($i % 38),
                'price' => $o['price'],
                'collection' => 'Mouture moyenne (filtre)',
                'brand' => ($i % 2 === 0) ? $arborealis : $cafeDuMonde,
                'tags' => [...$o['tags'], 'MOULU'],
            ], array_merge($specialty, array_slice($arabica, 0, 6)), array_keys(array_merge($specialty, array_slice($arabica, 0, 6)))),

            // --- Mouture grossière / piston (robusta + top arabica) ---
            ...array_map(fn ($o, $i) => [
                'name' => $o['name'].' Piston',
                'description' => '<p>'.$o['notes'].', mouture grossière pour cafetière à piston — 250g</p>',
                'sku' => 'MGS-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 8 + ($i % 32),
                'price' => $o['price'] + 20,
                'collection' => 'Mouture grossière (piston)',
                'brand' => ($i % 2 === 0) ? $arborealis : $blackPeak,
                'tags' => [...$o['tags'], 'MOULU'],
            ], array_merge($robusta, array_slice($arabica, 0, 6)), array_keys(array_merge($robusta, array_slice($arabica, 0, 6)))),

            // --- Capsules & dosettes (parent, blends + top arabica) ---
            ...array_map(fn ($b, $i) => [
                'name' => 'Capsule '.$b['name'],
                'description' => '<p>'.$b['notes'].', capsule compatible Nespresso® — x10</p>',
                'sku' => 'CPS-'.$b['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 20 + ($i % 50),
                'price' => max(490, (int) ($b['price'] * 0.4)),
                'collection' => 'Capsules & dosettes',
                'brand' => $cafeDuMonde,
                'tags' => [...$b['tags'], 'CAPSULE'],
            ], array_merge($blendList, array_slice($arabica, 0, 6)), array_keys(array_merge($blendList, array_slice($arabica, 0, 6)))),

            // --- Dosettes en plastique (arabica + top specialty) ---
            ...array_map(fn ($o, $i) => [
                'name' => 'Dosette '.$o['name'],
                'description' => '<p>'.$o['notes'].', dosette E.S.E 44mm — x18</p>',
                'sku' => 'DOS-'.$o['code'].'-'.str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'stock' => 25 + ($i % 50),
                'price' => max(690, (int) ($o['price'] * 0.55)),
                'collection' => 'Dosettes en plastique',
                'brand' => $cafeDuMonde,
                'tags' => [...$o['tags'], 'CAPSULE'],
            ], array_merge($arabica, array_slice($specialty, 0, 12)), array_keys(array_merge($arabica, array_slice($specialty, 0, 12)))),
        ];

        // Also seed the original hand-curated products (idempotent)
        $handCurated = $this->handCuratedProducts($arborealis, $cafeDuMonde, $blackPeak);
        $batches = [...$handCurated, ...$batches];

        foreach ($batches as $data) {
            $existingVariant = ProductVariant::where('sku', $data['sku'])->first();

            if ($existingVariant) {
                if ($existingVariant->prices()->doesntExist()) {
                    $existingVariant->prices()->create([
                        'currency_id' => $currencyId,
                        'customer_group_id' => null,
                        'price' => $data['price'],
                        'compare_price' => null,
                        'min_quantity' => 1,
                    ]);
                }

                $collectionName = $data['collection'];
                if (! isset($collectionCache[$collectionName])) {
                    $collectionCache[$collectionName] = Collection::get()->first(
                        fn (Collection $c) => $c->translateAttribute('name') === $collectionName
                    );
                }

                $collection = $collectionCache[$collectionName];
                if ($collection && $existingVariant->product->collections()->where('lunar_collections.id', $collection->id)->doesntExist()) {
                    $collection->products()->attach($existingVariant->product_id, ['position' => 1]);
                }

                continue;
            }

            $tagValues = array_unique($data['tags']);
            $tagIds = collect($tagValues)
                ->map(fn (string $value) => Tag::firstOrCreate(['value' => $value])->id)
                ->all();

            $product = Product::create([
                'product_type_id' => $productTypeId,
                'status' => 'published',
                'brand_id' => $data['brand']->id,
                'attribute_data' => [
                    'name' => new TranslatedText(collect(['en' => $data['name']])),
                    'description' => new TranslatedText(collect(['en' => $data['description']])),
                ],
            ]);

            $variant = $product->variants()->create([
                'tax_class_id' => $taxClassId,
                'sku' => $data['sku'],
                'stock' => $data['stock'],
                'purchasable' => 'always',
                'unit_quantity' => 1,
                'shippable' => true,
            ]);

            $variant->prices()->create([
                'currency_id' => $currencyId,
                'customer_group_id' => null,
                'price' => $data['price'],
                'compare_price' => null,
                'min_quantity' => 1,
            ]);

            $product->tags()->sync($tagIds);

            $collectionName = $data['collection'];
            if (! isset($collectionCache[$collectionName])) {
                $collectionCache[$collectionName] = Collection::get()->first(
                    fn (Collection $c) => $c->translateAttribute('name') === $collectionName
                );
            }

            $collection = $collectionCache[$collectionName];
            if ($collection) {
                $collection->products()->attach($product->id, ['position' => 1]);
            }
        }
    }

    /**
     * Hand-curated products from earlier episodes (idempotent).
     *
     * @return array<int, mixed>
     */
    private function handCuratedProducts(Brand $arborealis, Brand $cafeDuMonde, Brand $blackPeak): array
    {
        return [
            ['name' => 'Cafés en Grains Premium', 'description' => '<p>Sélection de grains d\'exception, torréfaction artisanale — 250g</p>', 'sku' => 'GRN-PRM-001', 'stock' => 50, 'price' => 1690, 'collection' => 'Cafés en grains', 'brand' => $cafeDuMonde, 'tags' => ['BLEND']],
            ['name' => 'Grains Bio Équitable', 'description' => '<p>Arabica bio issu du commerce équitable — 250g</p>', 'sku' => 'GRN-BIO-001', 'stock' => 22, 'price' => 1790, 'collection' => 'Cafés en grains', 'brand' => $arborealis, 'tags' => ['ARABICA', 'SINGLE-ORIGIN']],
            ['name' => 'Café Moulu Signature', 'description' => '<p>Assemblage maison prêt à l\'emploi, torréfaction moyenne — 250g</p>', 'sku' => 'CAF-MLD-001', 'stock' => 40, 'price' => 1190, 'collection' => 'Café moulus', 'brand' => $cafeDuMonde, 'tags' => ['BLEND', 'MOULU']],
            ['name' => 'Café Moulu Décaféiné', 'description' => '<p>Décaféiné naturel, saveur préservée — 250g</p>', 'sku' => 'CAF-DEC-001', 'stock' => 18, 'price' => 1390, 'collection' => 'Café moulus', 'brand' => $cafeDuMonde, 'tags' => ['BLEND', 'MOULU']],
            ['name' => 'Espresso Italiano Moulu', 'description' => '<p>Mouture fine pour espresso, crème onctueuse — 250g</p>', 'sku' => 'MLD-ESP-001', 'stock' => 30, 'price' => 1250, 'collection' => 'Mouture fine (espresso)', 'brand' => $cafeDuMonde, 'tags' => ['BLEND', 'MOULU']],
            ['name' => 'Éthiopie Espresso Moulu', 'description' => '<p>Floral et fruité, mouture fine — 250g</p>', 'sku' => 'MLD-ETH-001', 'stock' => 16, 'price' => 1490, 'collection' => 'Mouture fine (espresso)', 'brand' => $arborealis, 'tags' => ['ARABICA', 'MOULU', 'FLORAL']],
            ['name' => 'Arabica Filtre Moulu', 'description' => '<p>Idéal cafetière filtre, notes douces — 250g</p>', 'sku' => 'MLD-FLT-001', 'stock' => 28, 'price' => 1190, 'collection' => 'Mouture moyenne (filtre)', 'brand' => $arborealis, 'tags' => ['ARABICA', 'MOULU']],
            ['name' => 'Colombie Filtre', 'description' => '<p>Caramel, noisette, parfait pour le filtre — 250g</p>', 'sku' => 'MLD-COL-001', 'stock' => 20, 'price' => 1290, 'collection' => 'Mouture moyenne (filtre)', 'brand' => $cafeDuMonde, 'tags' => ['ARABICA', 'MOULU']],
            ['name' => 'Blend Piston Corsé', 'description' => '<p>Mouture grossière pour cafetière à piston — 250g</p>', 'sku' => 'MLD-PST-001', 'stock' => 24, 'price' => 1350, 'collection' => 'Mouture grossière (piston)', 'brand' => $blackPeak, 'tags' => ['BLEND', 'MOULU']],
            ['name' => 'Kenya Piston', 'description' => '<p>Cassis, tomate, magnifique en piston — 250g</p>', 'sku' => 'MLD-KEN-001', 'stock' => 14, 'price' => 1590, 'collection' => 'Mouture grossière (piston)', 'brand' => $arborealis, 'tags' => ['ARABICA', 'MOULU', 'FRUITÉ']],
            ['name' => 'Capsule Arabica Intense', 'description' => '<p>Capsule compatible Nespresso®, arabica intense — x10</p>', 'sku' => 'CAP-ARB-001', 'stock' => 45, 'price' => 490, 'collection' => 'Dosettes en plastique', 'brand' => $cafeDuMonde, 'tags' => ['ARABICA', 'CAPSULE']],
            ['name' => 'Capsule Colombie Douce', 'description' => '<p>Capsule compatible Nespresso®, douceur colombienne — x10</p>', 'sku' => 'CAP-COL-001', 'stock' => 38, 'price' => 490, 'collection' => 'Dosettes en plastique', 'brand' => $cafeDuMonde, 'tags' => ['ARABICA', 'CAPSULE']],
            ['name' => 'Dosette Espresso Classique', 'description' => '<p>Dosette E.S.E 44mm, espresso traditionnel — x18</p>', 'sku' => 'DOS-ESP-001', 'stock' => 52, 'price' => 890, 'collection' => 'Capsules & dosettes', 'brand' => $cafeDuMonde, 'tags' => ['BLEND', 'CAPSULE']],
            ['name' => 'Dosette Kenya AA', 'description' => '<p>Dosette E.S.E 44mm, Kenya AA sélectionné — x18</p>', 'sku' => 'DOS-KEN-001', 'stock' => 30, 'price' => 990, 'collection' => 'Capsules & dosettes', 'brand' => $arborealis, 'tags' => ['ARABICA', 'CAPSULE', 'FRUITÉ']],
        ];
    }
}
