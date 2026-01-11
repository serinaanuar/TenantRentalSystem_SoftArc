<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\User;

class PropertiesSeeder extends Seeder
{
    public function run()
    {
        // Get a seller user or create one if doesn't exist
        $seller = User::where('role', 'seller')->first();
        
        if (!$seller) {
            $seller = User::create([
                'firstname' => 'Demo',
                'lastname' => 'Seller',
                'email' => 'seller@demo.com',
                'password' => bcrypt('password'),
                'role' => 'seller',
                'phone' => '0123456789',
                'address_line_1' => '123 Main Street',
                'city' => 'Kuala Lumpur',
                'postal_code' => '50000',
            ]);
        }

        $properties = [
            [
                'user_id' => $seller->id,
                'username' => $seller->firstname . ' ' . $seller->lastname,
                'property_name' => 'City Center Studio',
                'property_address_line_1' => '12 Downtown Ave',
                'property_address_line_2' => 'Unit 3',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postal_code' => '40000',
                'purchase' => 'For Rent',
                'sale_type' => null,
                'property_type' => 'Conventional Condominium',
                'number_of_units' => 5,
                'square_feet' => 600,
                'price' => 1200.00,
                'each_unit_has_furnace' => true,
                'each_unit_has_electrical_meter' => true,
                'has_onsite_caretaker' => true,
                'parking' => 'Underground',
                'amenities' => json_encode(['Gym', 'Swimming Pool', 'Security']),
                'other_amenities' => 'Near LRT station',
                'additional_info' => 'Affordable studio apartments near public transport. Perfect for young professionals.',
                'approval_status' => 'Approved',
                'status' => 'available',
            ],
            [
                'user_id' => $seller->id,
                'username' => $seller->firstname . ' ' . $seller->lastname,
                'property_name' => 'Lakeside Luxury Apartment',
                'property_address_line_1' => '45 Lakeview Rd',
                'property_address_line_2' => 'Block B',
                'city' => 'Petaling Jaya',
                'state' => 'Selangor',
                'postal_code' => '46000',
                'purchase' => 'For Sale',
                'sale_type' => 'Subsale',
                'property_type' => 'Bare Land Condominium',
                'number_of_units' => 8,
                'square_feet' => 850,
                'price' => 250000.00,
                'each_unit_has_furnace' => true,
                'each_unit_has_electrical_meter' => true,
                'has_onsite_caretaker' => true,
                'parking' => 'Both',
                'amenities' => json_encode(['Gym', 'Swimming Pool', 'Playground', 'BBQ Area']),
                'other_amenities' => 'Lakefront view',
                'additional_info' => 'Close to shopping malls and schools. Beautiful lake view from balcony.',
                'approval_status' => 'Approved',
                'status' => 'available',
            ],
            [
                'user_id' => $seller->id,
                'username' => $seller->firstname . ' ' . $seller->lastname,
                'property_name' => 'Sunset Boulevard Office Loft',
                'property_address_line_1' => '78 Sunset Blvd',
                'property_address_line_2' => 'Unit 10',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan',
                'postal_code' => '50000',
                'purchase' => 'For Rent',
                'sale_type' => null,
                'property_type' => 'Commercial',
                'number_of_units' => 3,
                'square_feet' => 950,
                'price' => 3000.00,
                'each_unit_has_furnace' => false,
                'each_unit_has_electrical_meter' => true,
                'has_onsite_caretaker' => true,
                'parking' => 'Underground',
                'amenities' => json_encode(['24/7 Security', 'High-speed Internet', 'Conference Room']),
                'other_amenities' => 'Central business district',
                'additional_info' => 'Perfect for startups and small offices. Modern design with open floor plan.',
                'approval_status' => 'Approved',
                'status' => 'available',
            ],
            [
                'user_id' => $seller->id,
                'username' => $seller->firstname . ' ' . $seller->lastname,
                'property_name' => 'Greenfield Garden Residence',
                'property_address_line_1' => '23 Greenfield Lane',
                'property_address_line_2' => 'Unit 7',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postal_code' => '80000',
                'purchase' => 'For Sale',
                'sale_type' => 'New Launch',
                'property_type' => 'Conventional Condominium',
                'number_of_units' => 6,
                'square_feet' => 700,
                'price' => 180000.00,
                'each_unit_has_furnace' => true,
                'each_unit_has_electrical_meter' => true,
                'has_onsite_caretaker' => false,
                'parking' => 'Above ground',
                'amenities' => json_encode(['Garden', 'Playground', 'Jogging Track']),
                'other_amenities' => 'Near park',
                'additional_info' => 'Quiet neighborhood with parks nearby. Family-friendly environment.',
                'approval_status' => 'Approved',
                'status' => 'available',
            ],
            [
                'user_id' => $seller->id,
                'username' => $seller->firstname . ' ' . $seller->lastname,
                'property_name' => 'Riverside Commercial Hub',
                'property_address_line_1' => '9 Riverfront St',
                'property_address_line_2' => 'Unit 2',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'postal_code' => '75000',
                'purchase' => 'For Rent',
                'sale_type' => null,
                'property_type' => 'Commercial',
                'number_of_units' => 4,
                'square_feet' => 1200,
                'price' => 4000.00,
                'each_unit_has_furnace' => false,
                'each_unit_has_electrical_meter' => true,
                'has_onsite_caretaker' => true,
                'parking' => 'Above ground',
                'amenities' => json_encode(['Loading Bay', 'Storage Room', 'Display Windows']),
                'other_amenities' => 'Tourist area',
                'additional_info' => 'High foot traffic area, ideal for retail business. Prime location near tourist attractions.',
                'approval_status' => 'Approved',
                'status' => 'available',
            ],
        ];

        foreach ($properties as $property) {
            Property::create($property);
        }
    }
}
