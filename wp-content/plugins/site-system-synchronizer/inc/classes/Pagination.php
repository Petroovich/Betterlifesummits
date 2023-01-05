<?php
namespace SiteSystemSynchronizer;

class Pagination {
	/**
	 * Чистый URL по умолчанию
	 * В адресе может быть указано место для размещения блока с номером страницы, тег {page}
	 * Пример:
	 * /some_url{page}.html
	 * В итоге адрес будет:
	 * /some_url.html
	 * /some_url/page_2.html
	 * Если тег {page} не указан, то страницы будут дописываться в конец адреса
	 * @var string
	 */
	private $baseUrl = '/';

	/**
	 * Шаблон ссылки навигации
	 * @var string
	 */
	public $tpl = '&paged={page}';

	/**
	 * Обертка кнопок
	 * @var string
	 */
	public $wrap = '<ul class="pagination pagination-sm">{pages}</ul>';

	/**
	 * Сколько показывать кнопок страниц до и после актуальной
	 * Пример:
	 * $spread = 2
	 * Всего 9 страниц навигации и сейчас просматривают 5ю
	 * 1 ... 3 4 5 6 7 ... 9
	 * @var integer
	 */
	public $spread = 6;

	/**
	 * Разрыв между номерами страниц
	 * @var string
	 */
	public $separator = "<li><a>...</a></li>";

	/**
	 * Имя класса активной страницы
	 * @var string
	 */
	public $activeClass = 'link_active';

	public $parentActiveClass = 'active';

	/**
	 * Номер просматриваемой страницы
	 * @var integer
	 */
	private $currentPage = 0;

	/**
	 * Показывать кнопки "Вперед" и "Назад"
	 * @var bool
	 */
	public $nextPrev = true;

	/**
	 * Текст кнопки "Назад"
	 * @var string
	 */
	public $prevTitle = 'Prev';

	/**
	 * Текст кнопки "Вперед"
	 * @var string
	 */
	public $nextTitle = 'Next';

	/**
	 * Инициализация класса
	 *
	 * @param string $baseUrl URL в конец которого будет добавляться навигация
	 */

	public function __construct($baseUrl = '/'){
		$this->baseUrl = $baseUrl;
	}

	/**
	 * Строим навигации и формируем шаблон
	 *
	 * @param integer $limit количество записей на 1 страницу
	 * @param integer $count_all общее количество всех записей
	 * @param integer $currentPage номер просматриваемой страницы
	 *
	 * @return mixed Сформированный шаблон навигации готовый к выводу
	 */
	public function build($limit, $count_all, $currentPage = 1){
		if($limit < 1 OR $count_all <= $limit){
			return;
		}
		$count_pages = ceil($count_all / $limit);
		if($currentPage > $count_pages){
			header("HTTP/1.0 301 Moved Permanently");
			header("Location: ".$this->getUrl($count_pages));
			die("Redirect");
		}
		if($currentPage == 1 AND $_SERVER['REQUEST_URI'] != $this->getUrl($currentPage)){
			header("HTTP/1.0 301 Moved Permanently");
			header("Location: ".$this->getUrl($currentPage));
			die("Redirect");
		}

		$this->currentPage = intval($currentPage);
		if($this->currentPage < 1){
			$this->currentPage = 1;
		}

		$shift_start = max($this->currentPage - $this->spread, 2);
		$shift_end   = min($this->currentPage + $this->spread, $count_pages - 1);
		if($shift_end < $this->spread * 2){
			$shift_end = min($this->spread * 2, $count_pages - 1);
		}
		if($shift_end == $count_pages - 1 AND $shift_start > 3){
			$shift_start = max(3, min($count_pages - $this->spread * 2 + 1, $shift_start));
		}

		$list = $this->getItem(1);

		if($shift_start == 3){
			$list .= $this->getItem(2);
		}elseif($shift_start > 3){
			$list .= $this->separator;
		}

		for($i = $shift_start; $i <= $shift_end; $i++){
			$list .= $this->getItem($i);
		}

		$last_page = $count_pages - 1;
		if($shift_end == $last_page - 1){
			$list .= $this->getItem($last_page);
		}elseif($shift_end < $last_page){
			$list .= $this->separator;
		}

		$list .= $this->getItem($count_pages);

		if($this->nextPrev){
			$list = $this->getItem($this->currentPage > 1 ? $this->currentPage - 1 : 1, $this->prevTitle, true).$list.$this->getItem($this->currentPage < $count_pages ? $this->currentPage + 1 : $count_pages, $this->nextTitle, true);
		}

		return str_replace("{pages}", $list, $this->wrap);
	}

	/**
	 * Формирование адреса
	 *
	 * @param int $page_num номер страницы
	 *
	 * @return string сформированный адрес
	 */
	private function getUrl($page_num = 0){
		$page = $page_num > 1 ? str_replace('{page}', $page_num, $this->tpl) : '';

		if(stripos($this->baseUrl, '{page}') !== false){
			return str_replace('{page}', $page, $this->baseUrl);
		}else{
			return $this->baseUrl.$page;
		}
	}

	/**
	 * Формирование кнопки/ссылки
	 *
	 * @param int $page_num номер страницы
	 * @param string $page_name если указано, будет выводиться текст вместо номера страницы
	 * @param bool $noclass
	 *
	 * @return - span блок с активной страницей или ссылку.
	 */
	private function getItem($page_num, $page_name = '', $noclass = false){
		$page_name = $page_name ?: $page_num;
		$className = $noclass ? '' : $this->activeClass;
		$pclassName = $noclass ? '' : $this->parentActiveClass;

		if($this->currentPage == $page_num){
			return '<li class="'.$pclassName.'"><span class="'.$className.'">'.$page_name.'</span></li>';
		}else{
			return '<li><a href="'.$this->getUrl($page_num).'">'.$page_name.'</a></li>';
		}
	}
}