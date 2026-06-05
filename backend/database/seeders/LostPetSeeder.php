<?php

namespace Database\Seeders;

use App\Models\LostPet;
use App\Models\PetClue;
use App\Models\User;
use Illuminate\Database\Seeder;

class LostPetSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(3)->get();

        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        $pets = [
            [
                'pet_name' => '豆豆',
                'pet_type' => 'dog',
                'breed' => '金毛寻回犬',
                'color' => '金黄色',
                'collar_features' => '红色项圈带铃铛，有狗牌写着"豆豆"',
                'description' => '性格温顺，怕生人。走失时穿着蓝色小背心。',
                'last_seen_lat' => 39.9042,
                'last_seen_lng' => 116.4074,
                'last_seen_address' => '北京市东城区王府井大街附近',
                'last_seen_at' => now()->subDays(2),
                'contact_phone' => '13800138001',
                'contact_name' => '张先生',
                'status' => 'lost',
            ],
            [
                'pet_name' => '咪咪',
                'pet_type' => 'cat',
                'breed' => '英国短毛猫',
                'color' => '蓝灰色',
                'collar_features' => '粉色蝴蝶结项圈',
                'description' => '比较胆小，喜欢躲在车底或草丛里。',
                'last_seen_lat' => 39.9142,
                'last_seen_lng' => 116.4174,
                'last_seen_address' => '北京市东城区东四北大街小区',
                'last_seen_at' => now()->subDay(),
                'contact_phone' => '13800138002',
                'contact_name' => '李女士',
                'status' => 'lost',
            ],
            [
                'pet_name' => '旺财',
                'pet_type' => 'dog',
                'breed' => '中华田园犬',
                'color' => '黄白花',
                'collar_features' => '黑色皮革项圈',
                'description' => '已找到，感谢小区王阿姨的帮助！',
                'last_seen_lat' => 39.8942,
                'last_seen_lng' => 116.3974,
                'last_seen_address' => '北京市东城区前门大街',
                'last_seen_at' => now()->subDays(5),
                'contact_phone' => '13800138003',
                'contact_name' => '王先生',
                'status' => 'found',
                'thank_you_note' => '非常感谢小区的王阿姨在垃圾桶旁边发现了旺财，并联系了我！也感谢所有关心和帮助过我们的邻居们！旺财现在已经平安回家了。',
            ],
        ];

        foreach ($pets as $index => $petData) {
            $pet = LostPet::create(array_merge([
                'user_id' => $users[$index % $users->count()]->id,
                'view_count' => rand(10, 100),
            ], $petData));

            if ($pet->status === 'lost') {
                $clues = [
                    [
                        'lat' => $pet->last_seen_lat + 0.005,
                        'lng' => $pet->last_seen_lng + 0.005,
                        'address' => '东城区美术馆后街附近',
                        'seen_at' => $pet->last_seen_at->addHours(2),
                        'description' => '看到一只类似的狗在垃圾桶旁边找吃的',
                        'is_private' => false,
                        'user_id' => $users[($index + 1) % $users->count()]->id,
                    ],
                    [
                        'lat' => $pet->last_seen_lat - 0.003,
                        'lng' => $pet->last_seen_lng + 0.002,
                        'address' => '东城区沙滩北街XX号院',
                        'seen_at' => $pet->last_seen_at->addHours(5),
                        'description' => '在小区院子里看到过，当时有个穿灰衣服的人在附近',
                        'is_private' => true,
                        'user_id' => $users[($index + 2) % $users->count()]->id,
                    ],
                ];

                foreach ($clues as $clueData) {
                    PetClue::create($clueData);
                    $pet->increment('clue_count');
                }
            }
        }
    }
}
