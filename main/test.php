<?php

interface DeliveryServiceInterface
{
    public function calculateDeliveryCost(array $data): array;
}

class DeliveryCostCalculator
{
    private $deliveryServices;

    public function __construct(array $deliveryServices)
    {
        $this->deliveryServices = $deliveryServices;
    }

    public function calculateDeliveryCostForShipments(array $shipments, string $selectedService): array
    {
        $results = [];

        foreach ($shipments as $shipment) {
            if (isset($this->deliveryServices[$selectedService])) {
                $deliveryService = $this->deliveryServices[$selectedService];
                $result = $deliveryService->calculateDeliveryCost($shipment);
                $results[] = $result;
            }
        }

        return $results;
    }
}

class FastDeliveryService implements DeliveryServiceInterface
{
    private $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    public function calculateDeliveryCost(array $data): array
    {
        $basePrice = 300; // стоимость для быстрой доставки
        $weight = $data['weight']; // Вес  из параметров

        // Рассчитываем стоимость доставки на основе веса
        $coefficient = 2;
        $finalPrice = $basePrice + ($weight * $coefficient);

        // текушее время  в формате час:минута
        $currentTime = date('H:i');
        // Устанавливаем время, до которого принимаются заявки
        $cutoffTime = '18:00';

        if ($currentTime > $cutoffTime) {
            // если время больше чем 18:00 то доставка переноситься на след день
            $deliveryDate = date('Y-m-d', strtotime('+1 day'));
        } else {
            $deliveryDate = date('Y-m-d');
        }
        $result = [
            'sourceKladr' => 'Москва',
            'targetKladr' => 'Махачкала',
            'price' => $finalPrice,
            'date' => $deliveryDate,
            'error' => 'Ошибка', // можно указать ошибку если нужно уведомить пользователя
        ];

        return $result;
    }
}


class SlowDeliveryService implements DeliveryServiceInterface
{
    private $base_url;

    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    public function calculateDeliveryCost(array $data): array
    {
        $basePrice = 150; //  стоимость  медленной доставки
        $weight = $data['weight']; // Вес


        $coefficient = 1.2;
        $finalPrice = $basePrice + ($weight * $coefficient);

        // Определяем дату доставки (например, +3 дня от текущей даты)
        $deliveryDate = date('Y-m-d', strtotime('+3 days'));

        $result = [
            'sourceKladr' => 'Москва',
            'targetKladr' => 'Махачкала',
            'price' => $finalPrice,
            'date' => $deliveryDate,
            'error' => 'Ошибка', // можно указать ошибку если нужно уведомить пользователя
        ];

        return $result;
    }
}

// Создание объектов служб доставки
$fastDelivery = new FastDeliveryService('https://fast.delivery/api');
$slowDelivery = new SlowDeliveryService('https://slow.delivery/api');

// Создание калькулятора стоимости доставки
$deliveryServices = [
    'fast' => $fastDelivery,
    'slow' => $slowDelivery,
];
$calculator = new DeliveryCostCalculator($deliveryServices);


$shipmentsFast = [
    // быстрая доставка
    [
        'sourceKladr' => 'source_kladr_code_fast',
        'targetKladr' => 'target_kladr_code_fast',
        'weight' => 2.5,
    ],
];


$shipmentsSlow = [
    // медленная доставка
    [
        'sourceKladr' => 'source_kladr_code_slow',
        'targetKladr' => 'target_kladr_code_slow',
        'weight' => 1.8,
    ],
];

$selectedServiceFast = 'fast';
$resultsFast = $calculator->calculateDeliveryCostForShipments($shipmentsFast, $selectedServiceFast);

$selectedServiceSlow = 'slow';
$resultsSlow = $calculator->calculateDeliveryCostForShipments($shipmentsSlow, $selectedServiceSlow);

// вывод результатов для fast доставки
echo "Результат быстрой доставки:\n";
var_dump($resultsFast);
//смотря что выберет пользователь
// вывод результатов для slow доставки
echo "Результат медленной доставки:\n";
var_dump($resultsSlow);

