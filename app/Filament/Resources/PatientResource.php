<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Enums\GenderType;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\FileUpload::make('avatar')
                        ->image()
                        ->imageEditor(),
                    Forms\Components\TextInput::make('name')
                        ->required(),
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->native(false)
                        ->required()
                        ->closeOnDateSelection()
                        ->displayFormat('M d Y'),
                    Forms\Components\Select::make('type')
                        ->native(false)   
                        ->label('Gender')    
                        ->selectablePlaceholder(false)                 
                        ->options(GenderType::class),
                    Forms\Components\Select::make('clinic_id')
                        ->relationship('clinics', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable(),
                    Forms\Components\Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->native(false)
                        ->searchable()
                        ->preload()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('clinics.name')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date('M d Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->sortable()
                    ->searchable()

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('clinic_id')
                    ->label('Clinic')
                    ->relationship('clinics', 'name')
                    ->multiple()
                    ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Patient $record) {
                        // Delete file
                        Storage::delete('public/' . $record->avatar);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
