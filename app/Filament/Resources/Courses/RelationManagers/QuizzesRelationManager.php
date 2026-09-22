<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Quiz;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuizzesRelationManager extends RelationManager
{
    protected static string $relationship = 'quizzes';

    protected static ?string $title = 'الاختبارات والتقييمات (Quizzes)';

    protected static ?string $modelLabel = 'اختبار';

    protected static ?string $pluralModelLabel = 'الاختبارات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    TextInput::make('title')
                        ->label('عنوان الاختبار (Quiz Title)')
                        ->required()
                        ->placeholder('مثال: الاختبار النهائي للمقرر'),
                    TextInput::make('passing_percentage')
                        ->label('نسبة النجاح المطلوبة (%)')
                        ->numeric()
                        ->default(80.00)
                        ->required(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('time_limit_minutes')
                        ->label('الحد الزمني بالدقائق (اختياري)')
                        ->numeric()
                        ->placeholder('اتركه فارغاً إذا لم يكن هناك حد زمني'),
                    TextInput::make('max_attempts')
                        ->label('أقصى عدد محاولات مسموح')
                        ->numeric()
                        ->default(3)
                        ->required(),
                ]),

                Textarea::make('description')
                    ->label('تعليمات وإرشادات الاختبار (Description & Guidelines)')
                    ->columnSpanFull(),

                Repeater::make('questions')
                    ->relationship('questions')
                    ->label('أسئلة الاختبار (Questions)')
                    ->columnSpanFull()
                    ->reorderable('sort_order')
                    ->orderColumn('sort_order')
                    ->defaultItems(1)
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('question_text')
                                ->label('نص السؤال')
                                ->required()
                                ->columnSpan(2),
                            TextInput::make('points')
                                ->label('الدرجة')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ]),

                        Textarea::make('explanation')
                            ->label('توضيح أو تعليل الإجابة النموذجية (يظهر بعد التسليم)')
                            ->columnSpanFull(),

                        Repeater::make('options')
                            ->relationship('options')
                            ->label('خيارات الإجابة (Options)')
                            ->columnSpanFull()
                            ->minItems(2)
                            ->defaultItems(4)
                            ->reorderable('sort_order')
                            ->orderColumn('sort_order')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('option_text')
                                        ->label('نص الخيار')
                                        ->required()
                                        ->columnSpan(2),
                                    Toggle::make('is_correct')
                                        ->label('الإجابة الصحيحة')
                                        ->default(false),
                                ]),
                            ]),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description')->placeholder('-')->columnSpanFull(),
                TextEntry::make('passing_percentage')->suffix('%'),
                TextEntry::make('max_attempts'),
                TextEntry::make('time_limit_minutes')->placeholder('غير محدد'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان الاختبار')
                    ->searchable(),
                TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('عدد الأسئلة'),
                TextColumn::make('passing_percentage')
                    ->label('نسبة النجاح')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('max_attempts')
                    ->label('المحاولات')
                    ->sortable(),
                TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('عدد التسليمات'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
