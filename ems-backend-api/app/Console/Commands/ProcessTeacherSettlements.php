<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\StudentFee;
use App\Models\SystemSetting;
use Carbon\Carbon;

class ProcessTeacherSettlements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settlements:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically process teacher settlements based on configured date and deduction percentage';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Get settings from database
            $deductionPercentage = (float) SystemSetting::where('key', 'teacherFeeDeductionPercentage')->value('value') ?? 10;
            $settlementDay = (int) SystemSetting::where('key', 'automationSettlementDate')->value('value') ?? 10;

            // Check if today is the settlement day
            $currentDay = Carbon::now()->day;
            
            if ($currentDay != $settlementDay) {
                $this->info("Today is not settlement day. Settlement scheduled for day: {$settlementDay}");
                return;
            }

            $this->info("Processing teacher settlements for day {$settlementDay}");
            $this->info("Deduction percentage: {$deductionPercentage}%");

            // Get current month in Y-m format
            $currentMonth = Carbon::now()->format('Y-m');

            // Get all payments that haven't been settled for this month
            $payments = Payment::where('status', 'paid')
                ->where('month', $currentMonth)
                ->whereNull('teacher_settlement_processed_at')
                ->with(['student', 'course'])
                ->get();

            if ($payments->isEmpty()) {
                $this->info("No pending payments to settle for {$currentMonth}");
                return;
            }

            $totalSettled = 0;
            $settledCount = 0;

            foreach ($payments as $payment) {
                try {
                    // Calculate deduction amount
                    $deductionAmount = ($payment->amount * $deductionPercentage) / 100;
                    $teacherAmount = $payment->amount - $deductionAmount;

                    // Create teacher settlement record (you may need to create a TeacherSettlement model)
                    // For now, we'll update the payment record to mark it as settled
                    $payment->update([
                        'teacher_settlement_processed_at' => now(),
                        'teacher_deduction_percentage' => $deductionPercentage,
                        'teacher_deduction_amount' => $deductionAmount,
                        'teacher_net_amount' => $teacherAmount
                    ]);

                    $totalSettled += $teacherAmount;
                    $settledCount++;

                    $this->line("✓ Settled payment {$payment->id}: Teacher Amount = {$teacherAmount}");

                } catch (\Exception $e) {
                    $this->error("Failed to settle payment {$payment->id}: " . $e->getMessage());
                }
            }

            $this->info("=======================================");
            $this->info("Settlement Summary");
            $this->info("=======================================");
            $this->info("Total Payments Settled: {$settledCount}");
            $this->info("Total Teacher Amount: " . number_format($totalSettled, 2));
            $this->info("Deduction Percentage: {$deductionPercentage}%");
            $this->info("Settlement completed successfully!");

        } catch (\Exception $e) {
            $this->error("Error processing settlements: " . $e->getMessage());
            \Log::error("Teacher Settlement Error: " . $e->getMessage());
        }
    }
}
