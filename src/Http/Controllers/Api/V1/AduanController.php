<?php

namespace Bale\Frasasti\Http\Controllers\Api\V1;

use Bale\Api\Http\Controllers\Api\BaseApiController;
use Bale\Frasasti\Models\Aduan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AduanController extends BaseApiController
{
    /**
     * Daftar aduan masuk.
     *
     * Mendukung filter `status` (pending|processed|done), `category_id`, dan
     * pencarian `q` pada ref_code. Data yang dikembalikan sudah ter-dekripsi
     * (nama, NIP, nomor WhatsApp) karena cast `encrypted` bekerja di level model.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) max(1, min(
            $request->integer('per_page', config('frasasti.api.per_page', 50)),
            config('frasasti.api.max_per_page', 100)
        ));

        $aduan = Aduan::query()
            ->with(['category', 'handledBy'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('aduan_category_id', $request->string('category_id')))
            ->when($request->filled('q'), fn ($query) => $query->where('ref_code', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $aduan->setCollection(
            $aduan->getCollection()->map(fn (Aduan $item) => $this->transform($item))
        );

        return $this->jsonPaginated($aduan);
    }

    /**
     * Detail satu aduan.
     */
    public function show(Aduan $aduan): JsonResponse
    {
        $aduan->load(['category', 'handledBy']);

        return $this->jsonResponse($this->transform($aduan));
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(Aduan $aduan): array
    {
        return [
            'id' => $aduan->id,
            'ref_code' => $aduan->ref_code,
            'nama_lengkap' => $aduan->nama_lengkap,
            'nip' => $aduan->nip,
            'wa_number' => $aduan->wa_number,
            'deskripsi' => $aduan->deskripsi,
            'status' => $aduan->status,
            'status_label' => $aduan->status_label,
            'ip_address' => $aduan->ip_address,
            'category' => $aduan->category?->only(['id', 'name']),
            'handled_by' => $aduan->handledBy?->only(['uuid', 'username', 'name']),
            'handled_at' => optional($aduan->handled_at)->toDateTimeString(),
            'created_at' => optional($aduan->created_at)->toDateTimeString(),
            'updated_at' => optional($aduan->updated_at)->toDateTimeString(),
        ];
    }
}
