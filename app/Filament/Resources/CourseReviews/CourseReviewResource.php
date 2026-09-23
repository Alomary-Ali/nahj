<?php

namespace App\Filament\Resources\CourseReviews;

use App\Filament\Resources\CourseReviews\Pages\ListCourseReviews;
use App\Filament\Resources\CourseReviews\Tables\CourseReviewsTable;
use App\Models\CourseReview;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseReviewResource extends Resource
{
    protected static ?string $model = CourseReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $recordTitleAttribute = 'rating';

    protected static string|UnitEnum|null $navigationGroup = 'المجتمع والتقييمات';

    protected static ?string $navigationLabel = 'تقييمات المقررات (Reviews)';

    protected static ?string $modelLabel = 'تقييم ومراجعة';

    protected static ?string $pluralModelLabel = 'تقييمات المقررات';

    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return CourseReviewsTable::configure($table);
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
            'index' => ListCourseReviews::route('/'),
        ];
    }
}
