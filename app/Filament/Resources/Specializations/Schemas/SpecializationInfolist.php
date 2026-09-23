<?php

namespace App\Filament\Resources\Specializations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SpecializationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make('thumbnail')
                    ->label('صورة الغلاف')
                    ->circular()
                    ->defaultImageUrl('/backblue.gif'),
                TextEntry::make('title')
                    ->label('عنوان المسار')
                    ->weight('bold'),
                TextEntry::make('headline')
                    ->label('العنوان الفرعي')
                    ->placeholder('-'),
                TextEntry::make('instructor.name')
                    ->label('المدرب المشرف'),
                TextEntry::make('category.name')
                    ->label('التصنيف'),
                TextEntry::make('level')
                    ->label('المستوى')
                    ->badge(),
                IconEntry::make('is_published')
                    ->label('حالة النشر')
                    ->boolean(),
                TextEntry::make('description')
                    ->label('الوصف')
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ]);
    }
}
