<?php
namespace ModulesCore\modules\ProductsImport\Importers;

use ModulesCore\modules\ProductsImport\Snapshot\Snapshot;
use ModulesCore\modules\ProductsImport\Repositories\BrandFlyRepository;
use ModulesCore\modules\ProductsImport\Repositories\BrandMapRepository;
use ModulesCore\modules\ProductsImport\Services\ImageTransferService;

class BrandImporter
{
    private $brands;
    private $maps;
    private $images;
    public function __construct(BrandFlyRepository $brands, BrandMapRepository $maps, ImageTransferService $images)
    {
        $this->brands = $brands;
        $this->maps = $maps;
        $this->images = $images;
    }
    public function import(Snapshot $snapshot)
    {
        $map = [];
        foreach ($snapshot->getBrands() as $brand) {
            $sourceId = (int) $brand->id;
            $targetId = $this->maps->findFlyBrandId($sourceId);
            if ($targetId && !$this->brands->findById($targetId)) {
                $this->maps->delete($sourceId);
                $targetId = null;
            }
            if (!$targetId) {
                $targetId = $this->brands->findByName($brand->name);
                if (!$targetId) {
                    $data = ['name' => $brand->name];
                    if (isset($brand->url)) $data['url'] = $brand->url;
                    if (!empty($brand->image)) {
                        $data['image'] = $this->images->transferBrandLogo((string) $brand->image, $sourceId);
                    }
                    $targetId = $this->brands->create($data);
                }
                $this->maps->save($sourceId, $targetId);
            }
            $map[$sourceId] = $targetId;
        }
        return $map;
    }
}
