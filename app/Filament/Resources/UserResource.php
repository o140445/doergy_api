<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = '用户管理';
    protected static ?string $navigationGroup = 'Taskly 管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('邮箱')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('password')
                    ->label('密码')
                    ->password()
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),


                Forms\Components\Select::make('vip_type')
                    ->label('VIP 类型')
                    ->options([
                        0 => '普通用户',
                        1 => '月度 VIP',
                        2 => '年度 VIP',
                    ])
                    ->default(0)
                    ->required(),

                Forms\Components\DateTimePicker::make('vip_expire_at')
                    ->label('VIP 到期时间')
                    ->required()
                    ->default(now()->addYear()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('邮箱')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('任务数')
                    ->counts('tasks'),

                Tables\Columns\SelectColumn::make('vip_type')
                    ->label('VIP 类型')
                    ->options([
                        0 => '普通用户',
                        1 => '月度 VIP',
                        2 => '年度 VIP',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('vip_expire_at')
                    ->label('VIP 到期时间')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')->label('注册时间')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
