<?php

namespace PackBot;

use Longman\TelegramBot\Entities\InlineKeyboard;

class ScrollableKeyboard extends InlineKeyboard
{
    /**
     * The additional buttons to be added to the keyboard.
     */
    protected array $additionalButtons = [];

    /**
     * The current page of the keyboard.
     */
    protected int $currentPage = 1;

    /**
     * The number of entries per screen.
     */
    protected int $perScreen = 5;

    /**
     * The name of the screen that the keyboard is displayed on.
     */
    protected string $keyboardScreen = 'none';

    /**
     * The ID of the item that the screen is displaying.
     */
    protected int $screenItemID = 0;

    /**
     * The entries of the keyboard.
     */
    protected array $entries = [];

    /**
     * Creates a new scrollable inline keyboard.
     * This keyboard simulates a list of some elements that you can iterate between.
     *
     * Usage:
     * 1. Create a new instance of this class.
     * 2. Add entries to the keyboard using addEntries() method.
     *    Entries array structure: [
     *                                 [
     *                                    'text' => 'Text to be shown',
     *                                    'id'   => 1, // The ID of the entry. It should be unique for every entry.
     *                                                 // This ID will be passed to the callback data for selecting the entry.
     *                                ]
     *                           ]
     * 3. Set the number of entries per screen using setPerScreen() method. Default is 5.
     * 4. Set the screen name using setKeyboardScreen() method.
     * This name will be passed to the callback data for processing the keyboard and entries actions.
     * 5. Set the screen item ID using setScreenItemID() method. Use when the parent screen needs the ID of the entity it is displaying.
     * For example, an IncidentsListScreen with scrollable keyboard requires a site ID.
     * 6. Set the additional buttons using setAdditionalButtons() method. The buttons array is passed directly to the InlineKeyboard class.
     * 7. Set the current page using setCurrentPage() method. Default is 1.
     * 8. Get the keyboard using getKeyboard() method.
     *
     * Handling list actions:
     * 1. Define the logic for handling list actions. If an action is performed on the list, a listAction callback will be sent to the Screen.
     * The callback data will be in the following format: '<screenName>_listAction_<screenItemID>_<typeOfListAction>_<currentPage>'.
     * <typeOfListAction> can be 'nextPage' or 'previousPage'.
     * 2. Use nextPage() or previousPage() methods depending on the type of action.
     *    You can also check if there is a next or previous page using hasNextPage() and hasPreviousPage() methods.
     *
     * Handling entry actions:
     * 1. Define the logic for handling entry actions. If an action is performed on an entry, an entryAction callback will be sent to the Screen.
     * The callback data will be in the following format: '<screenName>_listItem_<entryID>'.
     *
     * @throws KeyboardException
     */
    public function __construct()
    {

    }

    /**
     * Adds entries to the keyboard.
     *
     * @param array $entries The entries to be added to the keyboard.
     */
    public function addEntries(array $entries): self
    {
        $this->entries = array_merge($this->entries, $entries);

        return $this;
    }

    /**
     * Sets the number of entries per screen.
     *
     * @param int $perScreen The number of elements per screen. Default is 5.
     */
    public function setPerScreen(int $perScreen): self
    {
        $this->perScreen = $perScreen;

        return $this;
    }

    /**
     * Sets the name of the screen that the keyboard is displayed on.
     *
     * @param string $screenName The name of the screen.
     */
    public function setKeyboardScreen(string $screenName): self
    {
        $this->keyboardScreen = $screenName;

        return $this;
    }

    /**
     * Sets the ID of the item that the screen is displaying.
     *
     * @param int $id The ID of the item.
     */
    public function setScreenItemID(int $id): self
    {
        $this->screenItemID = $id;

        return $this;
    }

    /**
     * Sets the additional buttons to be added to the keyboard.
     *
     */
    public function setAdditionalButtons(array $buttons)
    {
        $this->additionalButtons = $buttons;

        return $this;
    }

    /**
     * Sets the current page of the keyboard.
     *
     * @param int $page The current page of the keyboard.
     */
    public function setCurrentPage(int $page)
    {
        $this->currentPage = $page;

        return $this;
    }

    /**
     * Sets the keyboard to next page.
     */
    public function nextPage()
    {
        $this->currentPage++;

        return $this;
    }

    /**
     * Sets the keyboard to previous page.
     */
    public function previousPage()
    {
        $this->currentPage--;

        return $this;
    }

    /**
     * Getting the keyboard ready to be sent to the user.
     * Please note that this method should be called very last.
     *
     * @return InlineKeyboard The keyboard to be sent to the user.
     */
    public function getKeyboard(): InlineKeyboard
    {
        if (0 == count($this->entries)) {
            throw new KeyboardException('No entries were added to the keyboard.');
        }

        return $this->constructKeyboard();
    }

    public function hasNextPage()
    {
        $total       = count($this->entries);
        $pages       = ceil($total / $this->perScreen);
        $currentPage = $this->currentPage;

        return $currentPage < $pages;
    }

    public function hasPreviousPage()
    {
        return $this->currentPage > 1;
    }

    protected function constructKeyboard(): InlineKeyboard
    {

        $total       = count($this->entries);
        $pages       = ceil($total / $this->perScreen);
        $currentPage = $this->currentPage;
        $chunks      = array_chunk($this->entries, $this->perScreen, true);

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        if ($currentPage > $pages) {
            $currentPage = $pages;
        }

        $currentPageChunks = $chunks[$currentPage - 1];
        $entriesButtons    = $this->entriesToButtons($currentPageChunks);

        $navigationButtons = [
            [
                'text'          => $this->hasPreviousPage() ? '◀️' : '✖️',
                'callback_data' => $this->keyboardScreen . '_listAction_' . $this->screenItemID . '_previousPage_' . $currentPage,
            ],
            [
                'text'          => $currentPage . '/' . $pages,
                'callback_data' => 'ListSites_empty', //do nothing
            ],
            [
                'text'          => $this->hasNextPage() ? '▶️' : '✖️',
                'callback_data' => $this->keyboardScreen . '_listAction_' . $this->screenItemID . '_nextPage_' . $currentPage,
            ],
        ];

        return new InlineKeyboard(...array_merge($entriesButtons, [$navigationButtons], [$this->additionalButtons]));
    }

    protected function entriesToButtons(array $entries): array
    {
        $buttons = [];

        foreach ($entries as $entry) {
            if (!isset($entry['text']) || !isset($entry['id'])) {
                throw new KeyboardException('Invalid entry structure.');
            }
            $buttons[] = [
                [
                    'text'          => $entry['text'],
                    'callback_data' => $this->keyboardScreen . '_listItem_' . $entry['id'],
                ],
            ];
        }

        return $buttons;
    }
}
