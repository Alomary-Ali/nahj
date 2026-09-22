<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المقرر الأساسية (Basic Course Information)')
                    ->description('عنوان الدورة، الوصف الأكاديمي، ومخرجات التعلم.')
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان المقرر (Course Title)')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('headline')
                            ->label('العنوان الفرعي / الشعار (Headline)')
                            ->placeholder('مثال: تعلم بناء نماذج الذكاء الاصطناعي من الصفر خطوة بخطوة')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        RichEditor::make('description')
                            ->label('الوصف الأكاديمي الكامل ومخرجات التعلم (Description & Syllabus)')
                            ->columnSpanFull(),
                    ]),

                Section::make('التصنيف والمدرب (Category & Instructor)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('instructor_id')
                                    ->label('المدرب المعتمد (Instructor)')
                                    ->relationship('instructor', 'name')
                                    ->default(fn () => auth()->id())
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('category_id')
                                    ->label('التصنيف الأكاديمي (Category)')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('specialization_id')
                                    ->label('المسار / التخصص التابع له (Specialization)')
                                    ->relationship('specialization', 'title')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('المستوى، اللغة والتسعير (Metadata & Pricing)')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('level')
                                    ->label('المستوى الأكاديمي (Level)')
                                    ->options([
                                        'beginner' => 'مبتدئ (Beginner)',
                                        'intermediate' => 'متوسط (Intermediate)',
                                        'advanced' => 'متقدم (Advanced)',
                                        'all_levels' => 'كافة المستويات (All Levels)',
                                    ])
                                    ->default('beginner')
                                    ->required(),

                                Select::make('language')
                                    ->label('لغة المساق (Language)')
                                    ->options([
                                        'ar' => 'العربية (Arabic)',
                                        'en' => 'English',
                                    ])
                                    ->default('ar')
                                    ->required(),

                                TextInput::make('price')
                                    ->label('سعر الشهادة / الكورس (USD)')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('$')
                                    ->required(),

                                TextInput::make('duration_hours')
                                    ->label('المدة المقدرة بالساعات (Hours)')
                                    ->numeric()
                                    ->default(10)
                                    ->suffix('ساعة'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('preview_video_url')
                                    ->label('رابط الفيديو الترويجي (Trailer URL)')
                                    ->url()
                                    ->placeholder('https://...'),

                                Toggle::make('is_published')
                                    ->label('نشر المقرر للطلاب (Published)')
                                    ->helperText('عند التفعيل، سيظهر المقرر في كتالوج البحث للطلاب')
                                    ->default(false),
                            ]),

                        FileUpload::make('thumbnail')
                            ->label('صورة غلاف المقرر (Course Cover 16:9)')
                            ->image()
                            ->directory('courses/thumbnails')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
