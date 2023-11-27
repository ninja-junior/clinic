<?php

namespace App\Filament\Parent\Resources;

use App\Enums\GenderType;
use App\Filament\Parent\Resources\PatientResource\Pages;
use App\Filament\Parent\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->imageEditor(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\DatePicker::make('date_of_birth')
                   ->required(),
                Forms\Components\Select::make('type')
                    ->options(GenderType::class)
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        $parent = Filament::auth()->user();
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->parent($parent))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date(config('app.date_format')),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
