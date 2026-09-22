<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\CourseModule;
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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('عنوان الوحدة / الأسبوع (Module Title)')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('وصف الوحدة والمخرجات (Module Description)')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('الترتيب (Order)')
                    ->required()
                    ->numeric()
                    ->default(0),

                \Filament\Forms\Components\Repeater::make('lessons')
                    ->relationship('lessons')
                    ->label('دروس هذه الوحدة (Lessons)')
                    ->columnSpanFull()
                    ->reorderable('sort_order')
                    ->orderColumn('sort_order')
                    ->defaultItems(1)
                    ->schema([
                        \Filament\Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('title')
                                    ->label('عنوان الدرس')
                                    ->required()
                                    ->columnSpan(2),
                                \Filament\Forms\Components\Select::make('type')
                                    ->label('نوع الدرس')
                                    ->options([
                                        'video' => 'فيديو (Video)',
                                        'article' => 'مقال / قراءة (Article)',
                                        'quiz' => 'اختبار قصير (Quiz)',
                                    ])
                                    ->default('video')
                                    ->required(),
                            ]),

                        \Filament\Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('video_url')
                                    ->label('رابط الفيديو (HLS / MP4)')
                                    ->placeholder('https://...')
                                    ->columnSpan(2),
                                TextInput::make('duration_seconds')
                                    ->label('المدة بالثواني')
                                    ->numeric()
                                    ->default(300),
                            ]),

                        Textarea::make('content')
                            ->label('نص الدرس / المقال والملاحظات')
                            ->columnSpanFull(),

                        \Filament\Forms\Components\Toggle::make('is_preview')
                            ->label('متاح للمعاينة المجانية بدون تسجيل (Free Preview)')
                            ->default(false),
                    ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (CourseModule $record): bool => $record->trashed()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
