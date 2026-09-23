<?php

namespace App\Filament\Resources\Enrollments;

use App\Filament\Resources\Enrollments\Pages\ListEnrollments;
use App\Filament\Resources\Enrollments\Tables\EnrollmentsTable;
use App\Models\Enrollment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $recordTitleAttribute = 'id';

    protected static string|UnitEnum|null $navigationGroup = 'الطلاب والشهادات';

    protected static ?string $navigationLabel = 'تسجيلات الطلاب (Enrollments)';

    protected static ?string $modelLabel = 'تسجيل طالب';

    protected static ?string $pluralModelLabel = 'تسجيلات الطلاب';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return EnrollmentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && ! auth()->user()->hasRole('admin')) {
            $query->whereHas('course', fn ($q) => $q->where('instructor_id', auth()->id()));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnrollments::route('/'),
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
