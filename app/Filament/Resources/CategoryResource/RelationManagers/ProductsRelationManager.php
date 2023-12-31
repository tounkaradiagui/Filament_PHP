<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Enums\ProductType;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Produits')
                    ->tabs([
                        Tab::make('Informations')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur:true)
                                    ->unique()
                                    ->afterStateUpdated(function(string $operation, $state, Forms\Set $set) {
                                        // dd($operation);
                                        if($operation !== 'create'){
                                            return;
                                        }

                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                        ->required()
                                        ->disabled()
                                        ->dehydrated()
                                        ->unique(Product::class, 'slug', ignoreRecord:true),
                                MarkdownEditor::make('description')
                                        ->columnSpan('full')
                            ])->columns(2),
                        Tab::make('Prix et inventaire')
                            ->schema([
                                TextInput::make('sku')
                                ->label('SKU (Unité de gestion des stocks)')
                                ->unique()
                                ->required(),

                                TextInput::make('price')
                                    ->minValue(1)
                                    ->numeric()
                                    ->label('Prix')
                                    ->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->label('Quantité')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->required(),

                                Select::make('type')
                                    ->placeholder('Choisir un type')
                                    ->options([
                                        'deliverable' => ProductType::DELIVERABLE->value,
                                        'downloadable' => ProductType::DOWNLOADABLE->value,
                                    ]),
                            ])->columns(2),
                        Tab::make('Autres infos')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visibilité')
                                    ->helperText("Si cette option est désactivée, le produit ne sera pas visible sur la page d'accueil.")
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label('En Vedette')
                                    ->helperText("Une fois qu'un produit est en vedette, il apparait dans la section \"Nouveautés\" sur le site.
                                                    Il est possible qu'il soit affiché avant les produits classiques."),
                                DatePicker::make('published_at')
                                    ->label('Date de Publication')
                                    ->default(now()),

                                Select::make('brand_id')
                                    ->placeholder('Selectionner une marque')
                                    ->label('Marque')
                                    ->required()
                                    ->relationship('brand', 'name'),
                                FileUpload::make('image')
                                    ->directory('form-attachments')
                                    ->preserveFilenames()
                                    ->image()
                                    ->imageEditor()
                                    ->label('Image du produit')
                                    ->columnSpan('full')
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_visible')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('price')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('Produits')
                    ->label('Ajouter un produit')
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Modifier'),
                    Tables\Actions\ViewAction::make()
                        ->label('Voir'),
                    Tables\Actions\DeleteAction::make()
                        ->label('Supprimer'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
