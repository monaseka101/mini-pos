<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExport;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use Filament\Facades\Filament;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Auth;


class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-m-tag';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->html(),

                Tables\Columns\IconColumn::make('active')
                    ->sortable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->placeholder('All Categories')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->hidden(fn() => ! EditCategory::canEdit()),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->hidden(fn() => ! EditCategory::canEdit()),
                ]),
            ])
            ->headerActions([
                Action::make('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new CategoriesExport, 'categories.csv', \Maatwebsite\Excel\Excel::CSV);
                    }),

                Action::make('Export XLSX')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (): \Symfony\Component\HttpFoundation\BinaryFileResponse {
                        return Excel::download(new CategoriesExport, 'categories.xlsx');
                    }),
            ])
            ->recordUrl(function (Category $record) {
                return Filament::auth()->user()->role === Role::Admin
                    ? Pages\EditCategory::getUrl(['record' => $record])
                    : null;
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
    public static function canCreate(): bool
    {
        return Auth::user()?->role !== Role::Cashier;
    }
}
