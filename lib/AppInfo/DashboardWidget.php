<?php

declare(strict_types=1);

namespace OCA\Notes\AppInfo;

use OCA\Notes\Service\Note;
use OCA\Notes\Service\NotesService;
use OCP\Dashboard\IAPIWidget;
use OCP\Dashboard\IButtonWidget;
use OCP\Dashboard\IWidget;
use OCP\Dashboard\Model\WidgetItem;
use OCP\IL10N;
use OCP\IURLGenerator;

class DashboardWidget implements IWidget, IButtonWidget, IAPIWidget {
	private IURLGenerator $url;
	private IL10N $l10n;
	private NotesService $notesService;

	public function __construct(
		IURLGenerator $url,
		IL10N $l10n,
		NotesService $notesService
	) {
		$this->url = $url;
		$this->l10n = $l10n;
		$this->notesService = $notesService;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): string {
		return 'notes';
	}

	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->l10n->t('Notes');
	}

	/**
	 * @inheritDoc
	 */
	public function getOrder(): int {
		return 30;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconClass(): string {
		return 'icon-notes';
	}

	/**
	 * @inheritDoc
	 */
	public function getUrl(): ?string {
		return $this->url->linkToRouteAbsolute('notes.page.index');
	}

	/**
	 * @inheritDoc
	 */
	public function load(): void {
		\OCP\Util::addScript('notes', 'notes-dashboard');
	}

	public function getButtonUrl(): string {
		return $this->url->linkToRouteAbsolute('notes.page.create');
	}

	public function getButtonIconUrl(): ?string {
		return null;
	}

	public function getButtonText(): string {
		return $this->l10n->t('New notes');
	}

	public function getItems(string $userId, ?string $since = null, int $limit = 7): array {
		$notes = $this->notesService->getTopNotes($userId);
		$notes = array_slice($notes, 0, $limit);
		return array_map(function (Note $note) {
			$excerpt = '';
			try {
				$excerpt = $note->getExcerpt();
			} catch (\Throwable $e) {
			}
			$link = $this->url->linkToRouteAbsolute('notes.page.index', ['id' => $note->getId()]);
			$icon = $note->getFavorite() ? $this->url->getAbsoluteURL($this->url->imagePath('core', 'actions/starred.svg')) : '';
			return new WidgetItem($note->getTitle(), $excerpt, $link, $icon, (string)$note->getModified());
		}, $notes);
	}
}
