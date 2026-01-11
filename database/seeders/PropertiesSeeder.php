<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;

class PropertiesSeeder extends Seeder
{
    public function run()
    {
        $properties = [
        [
        'name' => 'City Center Studio',
        'agent_type' => 'Agent',
        'address_line_1' => '12 Downtown Ave',
        'address_line_2' => 'Unit 3',
        'city' => 'Shah Alam',
        'state' => 'Selangor',
        'postal_code' => '40000',
        'purchase_term' => 'For Rent',
        'property_type' => 'Conventional Condo',
        'number_of_units' => 5,
        'square_feet' => 600,
        'price' => 1200,
        'additional_info' => 'Affordable studio apartments near public transport.',
    ],
    [
        'name' => 'Lakeside Apartment',
        'agent_type' => 'Non Agent',
        'address_line_1' => '45 Lakeview Rd',
        'address_line_2' => 'Block B',
        'city' => 'Petaling Jaya',
        'state' => 'Selangor',
        'postal_code' => '46000',
        'purchase_term' => 'For Sale',
        'property_type' => 'Bare Land Condo',
        'number_of_units' => 8,
        'square_feet' => 850,
        'price' => 250000,
        'additional_info' => 'Close to shopping malls and schools.',
    ],
    [
        'name' => 'Sunset Boulevard Loft',
        'agent_type' => 'Agent',
        'address_line_1' => '78 Sunset Blvd',
        'address_line_2' => 'Unit 10',
        'city' => 'Kuala Lumpur',
        'state' => 'Wilayah Persekutuan',
        'postal_code' => '50000',
        'purchase_term' => 'For Rent',
        'property_type' => 'Commercial',
        'number_of_units' => 3,
        'square_feet' => 950,
        'price' => 3000,
        'additional_info' => 'Perfect for startups and small offices.',
    ],
    [
        'name' => 'Greenfield Residence',
        'agent_type' => 'Non Agent',
        'address_line_1' => '23 Greenfield Lane',
        'address_line_2' => 'Unit 7',
        'city' => 'Johor Bahru',
        'state' => 'Johor',
        'postal_code' => '80000',
        'purchase_term' => 'For Sale',
        'property_type' => 'Conventional Condo',
        'number_of_units' => 6,
        'square_feet' => 700,
        'price' => 180000,
        'additional_info' => 'Quiet neighborhood with parks nearby.',
    ],
    [
        'name' => 'Riverside Commercial Hub',
        'agent_type' => 'Agent',
        'address_line_1' => '9 Riverfront St',
        'address_line_2' => 'Unit 2',
        'city' => 'Melaka',
        'state' => 'Melaka',
        'postal_code' => '75000',
        'purchase_term' => 'For Rent',
        'property_type' => 'Commercial',
        'number_of_units' => 4,
        'square_feet' => 1200,
        'price' => 4000,
        'additional_info' => 'High foot traffic area, ideal for retail business.',
    ],
];

        foreach ($properties as $property) {
            Property::create($property);
        }
    }
}
