<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\GalleryImage;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home', [
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
            'promotions' => Promotion::active()->where('is_featured', true)->latest()->take(3)->get(),
            'testimonials' => Testimonial::where('is_active', true)->where('is_featured', true)->latest()->take(6)->get(),
            'gallery' => GalleryImage::where('is_active', true)->orderBy('sort_order')->take(8)->get(),
            'blogPosts' => BlogPost::published()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function services()
    {
        return view('public.services', [
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }
}
