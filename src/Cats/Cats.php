<?php

namespace PackBot;

class Cats
{
    protected int $userID;

    /**
     * An array of all paths to cat images.
     */
    protected array $cats;

    protected string $catsPath;

    /**
     * Array of file names (int without extension) that the user has seen.
     */
    protected array $catsSeen;

    public function __construct(int $userID)
    {
        $this->userID   = $userID;
        $this->catsPath = Path::toAssets() . '/cats/';
        $this->getCatsImgs();
        $this->getSeenImgs();
    }

    /**
     * Returns the path to a cat image that this user has not yet seen.
     * If there are no more cats, returns false.
     */
    public function get(): string|false
    {
        $catsIDS = [];

        foreach ($this->cats as $cat) {
            array_push($catsIDS, intval(pathinfo(basename($cat), PATHINFO_FILENAME)));
        }

        $diff = array_diff($catsIDS, $this->catsSeen);

        if (0 == count($diff)) {
            return false;
        }

        shuffle($diff);

        $randomUnseenImgID = $diff[0];

        CatsDB::addToSeen($this->userID, $randomUnseenImgID);

        foreach ($this->cats as $cat) {
            if (intval(pathinfo(basename($cat), PATHINFO_FILENAME)) == $randomUnseenImgID) {
                return $cat;
            }
        }

        throw new \Exception('Не удалось получить изображение кота.');
    }

    protected function getCatsImgs()
    {
        $types = ['jpg', 'jpeg', 'png', 'gif', 'mp4'];

        $allFiles = scandir($this->catsPath);

        if (!$allFiles) {
            throw new \Exception('Не удалось получить изображения котов.');
        }

        $cats = [];

        foreach ($allFiles as $file) {
            $fileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            /**
             * Check if file name (without extension) is number.
             */
            if (!is_numeric(pathinfo($file, PATHINFO_FILENAME))) {
                continue;
            }

            if (in_array($fileType, $types)) {
                array_push($cats, $this->catsPath . $file);
            }
        }

        if (0 == count($cats)) {
            throw new \Exception('Коты не найдены.');
        }

        $this->cats = $cats;
    }

    protected function getSeenImgs()
    {
        try {
            $this->catsSeen = CatsDB::getSeenImgs($this->userID);
        } catch (\PDOException) {
            throw new \Exception('Не удалось получить просмотренные изображения котов.');
        }
    }
}
