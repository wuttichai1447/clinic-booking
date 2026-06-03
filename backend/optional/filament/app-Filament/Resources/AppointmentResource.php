<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'การจอง';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'รอดำเนินการ',
                    'awaiting_payment' => 'รอชำระเงิน',
                    'confirmed' => 'ยืนยันแล้ว',
                    'cancelled' => 'ยกเลิก',
                ])
                ->required(),
            Forms\Components\TextInput::make('customer_name')->required(),
            Forms\Components\TextInput::make('customer_phone')->required(),
            Forms\Components\TextInput::make('customer_email')->email(),
            Forms\Components\Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->limit(8)->copyable(),
                Tables\Columns\TextColumn::make('customer_name')->searchable(),
                Tables\Columns\TextColumn::make('customer_phone'),
                Tables\Columns\TextColumn::make('date')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('clinic_id'),
                Tables\Columns\TextColumn::make('amount')->money('THB'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'awaiting_payment',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('paid_at')->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'awaiting_payment' => 'รอชำระ',
                    'confirmed' => 'ยืนยัน',
                    'cancelled' => 'ยกเลิก',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
