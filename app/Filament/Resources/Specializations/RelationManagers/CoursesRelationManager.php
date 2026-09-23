<?php

namespace App\Filament\Resources\Specializations\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoursesRelationManager extends RelationManager
{
    protected static string $relationship = 'courses';

    protected static ?string $title = 'المقررات التابعة للتخصص (Specialization Courses)';

    protected static ?string $modelLabel = 'مقرر دراسي';

    protected static ?string $pluralModelLabel = 'المقررات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان المقرر')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('الغلاف')
                    ->circular()
                    ->defaultImageUrl('/backblue.gif'),

                TextColumn::make('title')
                    ->label('عنوان المقرر')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('instructor.name')
                    ->label('المدرب'),

                TextColumn::make('level')
                    ->label('المستوى')
                    ->badge(),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD'),

                IconColumn::make('is_published')
                    ->label('منشور')
                    ->boolean(),
            ])
            ->headerActions([
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ]);
    }
}
