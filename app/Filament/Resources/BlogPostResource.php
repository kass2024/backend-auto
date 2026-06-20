<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\RestrictsStaffAccess;
use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    use RestrictsStaffAccess;

    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Website Content';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('category'),
            Forms\Components\Textarea::make('excerpt')->columnSpanFull(),
            Forms\Components\RichEditor::make('content')->columnSpanFull(),
            Forms\Components\TextInput::make('image')->label('Image URL'),
            Forms\Components\Toggle::make('is_published')->default(false),
            Forms\Components\DateTimePicker::make('published_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->limit(40),
            Tables\Columns\TextColumn::make('category'),
            Tables\Columns\IconColumn::make('is_published')->boolean()->label('Published'),
            Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])
          ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
