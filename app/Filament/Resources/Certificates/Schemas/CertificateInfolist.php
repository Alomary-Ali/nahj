<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CertificateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('certificate_code')
                    ->label('كود الشهادة')
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                TextEntry::make('user.name')
                    ->label('اسم الطالب'),
                TextEntry::make('course.title')
                    ->label('المقرر الأكاديمي'),
                TextEntry::make('instructor.name')
                    ->label('المدرب المعتمد'),
                TextEntry::make('grade_percentage')
                    ->label('الدرجة المحققة')
                    ->suffix('%')
                    ->badge()
                    ->color('success'),
                TextEntry::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date(),
                TextEntry::make('verification_hash')
                    ->label('البصمة الرقمية للتحقق')
                    ->copyable()
                    ->columnSpanFull(),
            ]);
    }
}
