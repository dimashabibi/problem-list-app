<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->enum('classification_problem', [
                'DD  -Construction Making',
                'DD - Stock List',
                'DD - Shutter making',
                'DD - Construction Interference',
                'DD - Global STD implementation',
                'DD - Component library',
                'DD - Dataout',
                'DD - Machining Attribute',
                'DD - CAD Data Using',
                'PM - Component installation',
                'PM - Material assy',
                'PM - Handwork',
                'PM - Identity marking',
                'PM - NC slice',
                'CC - DIE - ncdata process area',
                'CC - DIE - G-Code ',
                'CC - DIE - ncdata allowance',
                'CC - DIE - ncdata interference',
                'CC - DIE - ncdata amount',
                'CC - DIE - Overtravel data',
                'CC - BN - ncdata process area',
                'CC - BN - G-Code ',
                'CC - BN - ncdata allowance',
                'CC - BN - ncdata interference',
                'CC - BN - ncdata amount',
                'CC - BN - Overtravel data',
                'CC - KN - ncdata process area',
                'CC - KN - G-Code ',
                'CC - KN - ncdata allowance',
                'CC - KN - ncdata interference',
                'CC - KN - ncdata amount',
                'CC - KN - Overtravel data',
                'Mch - manual process',
                'Mch - Datum setting',
                'Mch - Machine Performance',
                'Mch - Dandori',
                'Mch - ncdata offset',
                'Mch - Ncdata transfer',
                'Mch - Dimension check',
                'Mch - Tool using',
                'DBCCA - Component order',
                'DBCCA - Component arrival time',
                'DBCCA - Component amount',
                'DF - Surface model',
                'DF - Profile',
                'QOH-Equipment',
                'QOH-X File',
                'QOH-Standard',
                'QOH-Process',
                'QOH-Misc Judgement',
                'Casting - blow hole',
                'casting - material minus',
                'DMCA-Distribution',
                'DMCA-Scheduling',
                'DEng-Equipment',
                'DEng-X File',
                'DEng-Standard',
                'DEng-Process',
                'DEng-Misc Judgement',
                'DAT - Assy Insert',
                'DAT - Assy komponen',
                'DAT - Welding  blow hole',
                'DAT - Assy shutter',
                'DAT - Interference komponen',
                'DAT - Polishing',
                'DAT - Flame hard (HRC)',
                'DAT - Tightening bolt'

            ])->nullable()->after('classification');
        });
    }

    public function down(): void
    {
        Schema::table('problems', function (Blueprint $table) {
            $table->dropColumn('classification_problem');
        });
    }
};
