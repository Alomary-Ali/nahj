<?php

namespace App\Filament\Resources\Specializations;

use App\Filament\Resources\Specializations\Pages\CreateSpecialization;
use App\Filament\Resources\Specializations\Pages\EditSpecialization;
use App\Filament\Resources\Specializations\Pages\ListSpecializations;
use App\Filament\Resources\Specializations\Pages\ViewSpecialization;
use App\Filament\Resources\Specializations\RelationManagers\CoursesRelationManager;
use App\Filament\Resources\Specializations\Schemas\SpecializationForm;
use App\Filament\Resources\Specializations\Schemas\SpecializationInfolist;
use App\Filament\Resources\Specializations\Tables\SpecializationsTable;
use App\Models\Specialization;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpecializationResource extends Resource
{
    protected static ?string $model = Specialization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'المناهج والمقررات';

    protected static ?string $navigationLabel = 'التخصصات والمسارات (Specializations)';

    protected static ?string $modelLabel = 'تخصص أكاديمي';

    protected static ?string $pluralModelLabel = 'التخصصات والمسارات';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SpecializationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SpecializationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecializationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CoursesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && ! auth()->user()->hasRole('admin')) {
            $query->where('instructor_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpecializations::route('/'),
            'create' => CreateSpecialization::route('/create'),
            'view' => ViewSpecialization::route('/{record}'),
            'edit' => EditSpecialization::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
