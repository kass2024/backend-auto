<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Pages\BasePrintableListRecords;
use App\Filament\Resources\BlogPostResource;
use Filament\Actions;

class ListBlogPosts extends BasePrintableListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getListDocumentTitle(): string
    {
        return 'BLOG POSTS';
    }

    protected function getListPrintKey(): string
    {
        return 'blog-posts';
    }

    protected function getResourceHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
