<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Booking;
use App\Models\GalleryImage;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Mechanic;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GarageSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@neamee-autotechsolutions.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1 (567) 329-9231',
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@neamee-autotechsolutions.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'phone' => '+1 (567) 329-9232',
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@neamee-autotechsolutions.com'],
            [
                'name' => 'James Mitchell',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 (567) 555-0142',
                'address' => '120 Bogle Lane, Bowling Green, KY 42101',
                'loyalty_points' => 320,
            ]
        );

        $services = [
            ['Engine Repair', 'engine-repair', 'Complete engine diagnostics, repair, and rebuild services.', 'engine', 149.99],
            ['Oil Change', 'oil-change', 'Premium oil change with multi-point inspection.', 'oil', 49.99],
            ['Brake Service', 'brake-service', 'Brake pad replacement, rotor resurfacing, and fluid flush.', 'brake', 89.99],
            ['Transmission Repair', 'transmission-repair', 'Automatic and manual transmission service and repair.', 'transmission', 199.99],
            ['Electrical Diagnosis', 'electrical-diagnosis', 'Advanced electrical system troubleshooting and repair.', 'electrical', 79.99],
            ['Wheel Alignment', 'wheel-alignment', 'Precision 4-wheel alignment for optimal handling.', 'alignment', 69.99],
            ['Tire Services', 'tire-services', 'Tire rotation, balancing, repair, and replacement.', 'tire', 39.99],
            ['Car Wash & Detailing', 'car-wash-detailing', 'Professional wash, wax, and interior detailing.', 'wash', 59.99],
            ['Air Conditioning Service', 'ac-service', 'A/C recharge, leak detection, and compressor repair.', 'ac', 99.99],
            ['Battery Replacement', 'battery-replacement', 'Battery testing, replacement, and terminal cleaning.', 'battery', 129.99],
        ];

        foreach ($services as $i => [$name, $slug, $desc, $icon, $price]) {
            Service::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $desc,
                    'icon' => $icon,
                    'price_from' => $price,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }

        Promotion::updateOrCreate(
            ['title' => 'Spring Service Special'],
            [
                'description' => 'Get 20% off any full service package this season. Includes oil change, tire rotation, and brake inspection.',
                'discount_percent' => 20,
                'is_featured' => true,
                'is_active' => true,
                'starts_at' => now()->startOfMonth(),
                'ends_at' => now()->addMonths(2),
            ]
        );

        Promotion::updateOrCreate(
            ['title' => 'New Customer Discount'],
            [
                'description' => 'First-time customers receive $25 off their first service appointment.',
                'discount_amount' => 25,
                'is_featured' => true,
                'is_active' => true,
            ]
        );

        $testimonials = [
            ['James Mitchell', 5, 'Outstanding service! They diagnosed my engine issue quickly and had me back on the road in two days. Highly recommend NEAMEE Auto-Tech.', '2021 Ford F-150'],
            ['Sarah Johnson', 5, 'Professional team, fair pricing, and they kept me updated throughout the entire repair process. Best garage in Bowling Green!', '2019 Toyota Camry'],
            ['Michael Davis', 5, 'I trust them with all my fleet vehicles. Genuine parts, certified mechanics, and excellent customer support.', 'Commercial Fleet'],
            ['Emily Carter', 5, 'The online booking was so easy and the car wash & detailing service exceeded my expectations. Will definitely return!', '2022 Honda CR-V'],
        ];

        foreach ($testimonials as [$name, $rating, $review, $vehicle]) {
            Testimonial::updateOrCreate(
                ['customer_name' => $name, 'review' => $review],
                ['rating' => $rating, 'vehicle_info' => $vehicle, 'is_featured' => true, 'is_active' => true]
            );
        }

        $gallery = [
            ['Engine Bay Repair', '/images/scroll/engine.jpg', 'workshop', 'workshop'],
            ['Brake Service', '/images/scroll/brake.jpg', 'workshop', 'general'],
            ['Before Repair', '/images/scroll/diagnostic.jpg', 'repair', 'before'],
            ['After Repair', '/images/scroll/car.jpg', 'repair', 'after'],
            ['Modern Workshop', '/images/scroll/workshop.jpg', 'workshop', 'workshop'],
            ['Tire Service', '/images/scroll/tire.jpg', 'workshop', 'general'],
        ];

        foreach ($gallery as $i => [$title, $image, $cat, $type]) {
            GalleryImage::updateOrCreate(
                ['title' => $title],
                ['image' => $image, 'category' => $cat, 'type' => $type, 'sort_order' => $i + 1, 'is_active' => true]
            );
        }

        $posts = [
            [
                '5 Essential Car Maintenance Tips for Longevity',
                'car-maintenance-tips',
                'Keep your vehicle running smoothly with these expert maintenance tips from our certified mechanics.',
                'Regular maintenance is the key to extending your vehicle\'s lifespan and avoiding costly repairs. Here are five essential tips every car owner should follow...',
                'Maintenance Tips',
            ],
            [
                'How to Save Fuel: 7 Proven Strategies',
                'fuel-saving-tips',
                'Reduce your fuel costs with these practical driving and maintenance strategies.',
                'Rising fuel prices affect every driver. Learn how proper tire inflation, smooth driving habits, and regular tune-ups can significantly improve your fuel economy...',
                'Fuel Saving',
            ],
            [
                'Tire Maintenance: When to Rotate and Replace',
                'tire-maintenance-guide',
                'Everything you need to know about tire care, rotation schedules, and replacement indicators.',
                'Your tires are the only contact between your vehicle and the road. Proper tire maintenance ensures safety, performance, and fuel efficiency...',
                'Tire Maintenance',
            ],
            [
                'Engine Care: Signs Your Engine Needs Attention',
                'engine-care-signs',
                'Learn to recognize early warning signs of engine problems before they become major repairs.',
                'Unusual noises, warning lights, and performance changes can indicate engine issues. Our guide helps you identify problems early and know when to visit a professional...',
                'Engine Care',
            ],
        ];

        foreach ($posts as [$title, $slug, $excerpt, $content, $category]) {
            BlogPost::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $content,
                    'category' => $category,
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 30)),
                ]
            );
        }

        Mechanic::updateOrCreate(
            ['name' => 'Mike Thompson'],
            ['email' => 'mike@neamee-autotechsolutions.com', 'specialties' => ['engine', 'transmission'], 'is_available' => true]
        );

        Mechanic::updateOrCreate(
            ['name' => 'David Rodriguez'],
            ['email' => 'david@neamee-autotechsolutions.com', 'specialties' => ['brake', 'electrical', 'ac'], 'is_available' => true]
        );

        $this->seedCustomerDemoData($customer);
    }

    private function seedCustomerDemoData(User $customer): void
    {
        $brakeService = Service::where('slug', 'brake-service')->first();
        $oilService = Service::where('slug', 'oil-change')->first();
        $mechanic = Mechanic::where('name', 'Mike Thompson')->first();

        if (! $brakeService || ! $oilService) {
            return;
        }

        $vehicle = Vehicle::updateOrCreate(
            ['user_id' => $customer->id, 'plate_number' => 'KY-F1501'],
            [
                'make' => 'Ford',
                'model' => 'F-150',
                'year' => 2021,
                'color' => 'Blue',
                'mileage' => 45200,
            ]
        );

        $booking = Booking::updateOrCreate(
            ['reference' => 'BK-DEMO-001'],
            [
                'user_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'service_id' => $brakeService->id,
                'mechanic_id' => $mechanic?->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'scheduled_date' => now()->addDays(3)->toDateString(),
                'scheduled_time' => '10:00:00',
                'status' => 'confirmed',
                'notes' => 'Customer reported squeaking brakes.',
            ]
        );

        Booking::updateOrCreate(
            ['reference' => 'BK-DEMO-002'],
            [
                'user_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'service_id' => $oilService->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'scheduled_date' => now()->subDays(14)->toDateString(),
                'scheduled_time' => '14:30:00',
                'status' => 'completed',
            ]
        );

        $jobCard = JobCard::updateOrCreate(
            ['job_number' => 'JOB-DEMO-001'],
            [
                'booking_id' => $booking->id,
                'user_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'mechanic_id' => $mechanic?->id,
                'inspection_notes' => 'Front brake pads at 20%. Rotors within spec.',
                'labor_cost' => 120.00,
                'parts_cost' => 89.99,
                'total_cost' => 209.99,
                'status' => 'in_progress',
                'started_at' => now()->subHours(4),
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INV-DEMO-001'],
            [
                'user_id' => $customer->id,
                'job_card_id' => $jobCard->id,
                'subtotal' => 189.99,
                'tax_rate' => 6.00,
                'tax_amount' => 11.40,
                'total' => 201.39,
                'status' => 'sent',
                'due_date' => now()->addDays(7)->toDateString(),
            ]
        );
    }
}
