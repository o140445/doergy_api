<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{

    protected static ?string $model = Task::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = '任务管理';
    protected static ?string $navigationGroup = 'Taskly 管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->rows(4),

                Forms\Components\Select::make('status')
                    ->label('状态')
                    ->options([
                        'pending' => '待办',
                        'in_progress' => '进行中',
                        'completed' => '已完成',
                    ])
                    ->required(),

                Forms\Components\Select::make('priority')
                    ->label('优先级')
                    ->options([
                        'low' => '低',
                        'medium' => '中',
                        'high' => '高',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('due_date')
                    ->label('截止时间'),

                Forms\Components\Select::make('eisenhower_type')
                    ->label('艾森豪威尔象限')
                    ->options([
                        1 => '重要且紧急',
                        2 => '重要不紧急',
                        3 => '紧急不重要',
                        4 => '不重要不紧急',
                    ])
                    ->required(),

                Forms\Components\Select::make('project_id')
                    ->label('所属项目')
                    ->relationship('project', 'name')
                    ->searchable(),

                Forms\Components\Select::make('user_id')
                    ->label('所属用户')
                    ->relationship('user', 'name')
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('标题')->searchable(),
                Tables\Columns\TextColumn::make('project.name')->label('项目'),
                Tables\Columns\TextColumn::make('user.name')->label('用户'),
                Tables\Columns\TextColumn::make('priority')->label('优先级'),
                Tables\Columns\TextColumn::make('status')->label('状态'),
                Tables\Columns\TextColumn::make('due_date')->label('截止')->date(),
                Tables\Columns\TextColumn::make('eisenhower_type')->label('象限')->formatStateUsing(function ($state) {
                    return match ($state) {
                        1 => '重要且紧急',
                        2 => '重要不紧急',
                        3 => '紧急不重要',
                        4 => '不重要不紧急',
                        default => '未分类',
                    };
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('eisenhower_type')
                    ->label('象限筛选')
                    ->options([
                        1 => '重要且紧急',
                        2 => '重要不紧急',
                        3 => '紧急不重要',
                        4 => '不重要不紧急',
                    ]),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
