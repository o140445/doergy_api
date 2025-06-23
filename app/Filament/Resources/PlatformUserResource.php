<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformUserResource\Pages;
use App\Filament\Resources\PlatformUserResource\RelationManagers;
use App\Models\PlatformUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlatformUserResource extends Resource
{
    protected static ?string $model = PlatformUser::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = '平台用户';
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
                    ->dehydrated(fn ($state) => filled($state)),

                Forms\Components\Select::make('vip_type')
                    ->label('VIP 类型')
                    ->options(PlatformUser::getVipTypes())
                    ->required(),

                Forms\Components\DatePicker::make('vip_expiration')
                    ->label('VIP 到期时间')
                    ->nullable(),

                Forms\Components\Textarea::make('settings')
                    ->label('用户设置')
                    ->placeholder('JSON 格式的用户设置，如暗黑模式、语言偏好等')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->label('邮箱')->searchable(),
                //vip_type 不同值对应不同的文本颜色
                Tables\Columns\TextColumn::make('vip_type')
                    ->label('VIP 类型')
                    ->formatStateUsing(fn ($state) => PlatformUser::getVipTypeLabel($state))
                    ->color(fn ($state) => match ($state) {
                        PlatformUser::VIP_TYPE_FREE => 'gray',
                        PlatformUser::VIP_TYPE_MONTHLY => 'primary',
                        PlatformUser::VIP_TYPE_YEARLY => 'success',
                        default => 'gray',
                    }),
                // 格式 Y-m-d vip_expiration
                Tables\Columns\TextColumn::make('vip_expiration')
                    ->label('VIP 到期时间')
                    ->date('Y-m-d'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('注册时间')
                    ->dateTime('Y-m-d H:i:s'),
            ])
            ->filters([
                // 1. 邮箱
                Tables\Filters\Filter::make('email')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('邮箱')
                            ->email()
                            ->placeholder('输入邮箱进行过滤'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->where('email', 'like', '%' . $data['email'] . '%')),

                // 2. VIP 类型筛选
                Tables\Filters\SelectFilter::make('vip_type')
                    ->label('VIP 类型')
                    ->options(PlatformUser::getVipTypes()),

                // 3. 注册时间筛选
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('注册开始时间'),
                        Forms\Components\DatePicker::make('created_until')->label('注册结束时间'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),

                // 4. VIP 到期时间筛选
                Tables\Filters\Filter::make('vip_expiration')
                    ->label('VIP 到期时间')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('VIP 到期开始日期')->placeholder('选择开始日期'),
                        Forms\Components\DatePicker::make('end_date')->label('VIP 到期结束日期')->placeholder('选择结束日期'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            return $query->whereBetween('vip_expiration', [
                                $data['start_date'] . ' 00:00:00',
                                $data['end_date'] . ' 23:59:59',
                            ]);
                        }
                        return $query;
                    }),
            ])->filtersLayout(Tables\Enums\FiltersLayout::AboveContent) // 👈 把筛选器表单放在表格上方
            ->filtersFormColumns(2) // 👈 每行显示两个筛选字段
            ->persistFiltersInSession() // 👈 记住用户上次使用的筛选条件
            ->actions([
                Tables\Actions\EditAction::make(),
                // 删除操作
                Tables\Actions\DeleteAction::make()
                    ->label('删除用户')
                    ->modalHeading('删除用户')
                    ->modalSubheading('您确定要删除此用户吗？此操作不可逆。')
                    ->successNotificationTitle('用户已删除')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPlatformUsers::route('/'),
            'create' => Pages\CreatePlatformUser::route('/create'),
            'edit' => Pages\EditPlatformUser::route('/edit/{record}'),
        ];
    }
}
