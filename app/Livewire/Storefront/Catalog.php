<?php

declare(strict_types=1);

namespace App\Livewire\Storefront;

use App\Models\Category;
use App\Models\Collection;
use App\Services\Storefront\CatalogService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Catálogo público con filtros reactivos.
 *
 * Una sola página sirve a /tienda, /novedades, /ofertas, /categoria/{slug}
 * y /coleccion/{slug}. El contexto se deduce del nombre de ruta y de los
 * parámetros enlazados (category/collection).
 */
#[Layout('components.layouts.storefront')]
class Catalog extends Component
{
    use WithPagination;

    public ?int $categoryId = null;

    public ?int $collectionId = null;

    public bool $onlyNew = false;

    public bool $onlySale = false;

    public string $contextTitle = 'Tienda';

    public ?string $contextEyebrow = 'Catálogo';

    public ?string $contextDescription = null;

    #[Url(as: 'q')]
    public string $search = '';

    /** @var list<string> */
    #[Url]
    public array $colors = [];

    /** @var list<string> */
    #[Url]
    public array $sizes = [];

    #[Url]
    public string $sort = 'relevancia';

    public function mount(?Category $category = null, ?Collection $collection = null): void
    {
        $routeName = request()->route()?->getName();

        if ($category !== null && $category->exists) {
            $this->categoryId = $category->id;
            $this->contextEyebrow = 'Categoría';
            $this->contextTitle = $category->name;
            $this->contextDescription = $category->description;
        } elseif ($collection !== null && $collection->exists) {
            $this->collectionId = $collection->id;
            $this->contextEyebrow = 'Colección';
            $this->contextTitle = $collection->name;
            $this->contextDescription = $collection->description;
        } elseif ($routeName === 'catalog.new') {
            $this->onlyNew = true;
            $this->contextEyebrow = 'Recién llegado';
            $this->contextTitle = 'Novedades';
            $this->contextDescription = 'Lo último para que entrenes con estilo y actitud.';
        } elseif ($routeName === 'catalog.sale') {
            $this->onlySale = true;
            $this->contextEyebrow = 'Ofertas';
            $this->contextTitle = 'Ofertas';
            $this->contextDescription = 'Prendas seleccionadas a precio especial.';
        } else {
            $this->contextDescription = 'Ropa deportiva premium para mujer, diseñada para entrenar y moverte.';
        }
    }

    public function updating(): void
    {
        $this->resetPage();
    }

    public function toggleColor(string $color): void
    {
        $this->colors = $this->toggleValue($this->colors, $color);
        $this->resetPage();
    }

    public function toggleSize(string $size): void
    {
        $this->sizes = $this->toggleValue($this->sizes, $size);
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'colors', 'sizes']);
        $this->sort = 'relevancia';
        $this->resetPage();
    }

    public function render(CatalogService $catalog): View
    {
        $products = $catalog->paginate([
            'category_id' => $this->categoryId,
            'collection_id' => $this->collectionId,
            'only_new' => $this->onlyNew,
            'only_sale' => $this->onlySale,
            'colors' => $this->colors,
            'sizes' => $this->sizes,
            'search' => $this->search !== '' ? $this->search : null,
            'sort' => $this->sort,
        ]);

        return view('livewire.storefront.catalog', [
            'products' => $products,
            'categories' => $catalog->categories(),
            'colorOptions' => $catalog->attributeValues('color'),
            'sizeOptions' => $catalog->attributeValues('talla'),
            'hasFilters' => $this->search !== '' || $this->colors !== [] || $this->sizes !== [],
        ]);
    }

    /**
     * @param  list<string>  $list
     * @return list<string>
     */
    private function toggleValue(array $list, string $value): array
    {
        if (in_array($value, $list, true)) {
            return array_values(array_filter($list, fn (string $v): bool => $v !== $value));
        }

        $list[] = $value;

        return $list;
    }
}
