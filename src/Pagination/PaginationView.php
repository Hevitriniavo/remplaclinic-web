<?php
namespace App\Pagination;

class PaginationView
{
    public function __construct(
        private readonly Pagination $pagination,
        private readonly ?string $url = '',
    )
    {}

    public function renderLimitOptions(int $totalRecords): string
    {
        $limitOptions = $this->pagination->limitOptions;
        $limit = $this->pagination->limit;
        $offset = $this->pagination->offset;

        $optionHtml = [];
        foreach ($limitOptions as $limitOption) {
            $selected = $limitOption === $limit ? ' selected' : '';
            $optionHtml[] = sprintf('<option value="%d"%s>%d</option>', $limitOption, $selected, $limitOption);
        }

        $pageSelectionView = sprintf(
            'Afficher <select name="limit" class="outline-none border-[1px] border-solid border-[#eaeaea] h-50 w-100 p-2 rounded-md bg-transparent">%s</select> sur <span>%d</span> lignes (de <span>%d</span> à <span>%d</span> lignes)',
            implode('', $optionHtml),
            $totalRecords,
            $offset + 1,
            min($offset + $limit, $totalRecords)
        );

        return $pageSelectionView;
    }

    public function renderPageLinks(int $totalRecords): string
    {
        $page = $this->pagination->page;
        $maxPage = $this->pagination->getMaxPage($totalRecords);
        $url = $this->getUrl();
        
        $pages = $this->pagination->paginate($totalRecords, 5);

        $pageLinksHtml = [];

        // previous page link
        $pageLinksHtml[] = $this->getPageLink($page - 1, $url, false, $page <= 1, '&lt;');
        
        // first page
        foreach ($pages as $p) {
            if ($p === '...') {
                $pageLinksHtml[] = $this->getPageLink(0, $url, false, false, '...');
            } else {
                $pageLinksHtml[] = $this->getPageLink($p, $url, $p === $page, false);
            }
        }

        // next page
        $pageLinksHtml[] = $this->getPageLink($page + 1, $url, false, $page >= $maxPage, '&gt;');

        return implode('', $pageLinksHtml);
    }

    public function render(int $totalRecords): string
    {
        $limitOptionsView = $this->renderLimitOptions($totalRecords);
        $pageLinksView = $this->renderPageLinks($totalRecords);

        return sprintf(
            '<div class="flex justify-between items-center gap-2 mt-2"><div>%s</div><div>%s</div></div>',
            $limitOptionsView,
            $pageLinksView
        );
    }

    private function getUrl(): string
    {
        return is_null($this->url) ? '' : $this->url;
    }

    private function getPageLink(int $page, string $url, bool $active = false, bool $disabled = false, string $pageText = ''): string
    {
        $baseUrl = '';
        if (stripos($url, '?') === false) {
            $baseUrl = $url . '?';
        } else {
            $baseUrl = $url . '&';
        }

        if (empty($pageText)) {
            $pageText = $page;
        }

        $activeClass = $active ? 'border-[#86d0f0] bg-[#ebf6fc] text-[#2d8eb8]' : 'border-[#eaeaea]';
        $startTag = $disabled ? 'span' : sprintf('a href="%spage=%s"', $baseUrl, $page);
        $endTag = $disabled ? 'span' : 'a';

        return sprintf(
            '<%s class="p-2 border-[1px] border-solid %s">%s</%s>',
            $startTag,
            $activeClass,
            $pageText,
            $endTag
        );
    }
}