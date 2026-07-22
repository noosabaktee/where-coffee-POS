<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outlet_id' => $this->outlet_id,
            'storeName' => $this->store_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'taxRate' => (float) $this->tax_rate,
            'serviceCharge' => (float) $this->service_charge_rate,
            'storeLogo' => $this->logo,
            'qrisImage' => $this->qris_image,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'receiptFooter' => $this->receipt_footer,
            'pointsPerAmount' => $this->points_per_amount,
            'pointValue' => $this->point_value,
        ];
    }
}
