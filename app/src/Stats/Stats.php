<?php
declare(strict_types=1);

namespace Stats;

use ApiClient\Posts;
use DataTransformer\PostTransformer;
use Exception\AppException;

class Stats
{
    protected const FIELD_LENGTH_PER_MONTH = 'average_post_length_per_month';
    protected const FIELD_LONGEST_POST_PER_MONTH = 'longest_post_length_per_month';
    protected const FIELD_TOTAL_PER_WEEK = 'total_posts_per_week';
    protected const FIELD_USER_PER_MONTH = 'average_posts_per_user_per_month';
    protected const FIELD_USERS = 'users';

    protected array $totals = [];

    /**
     * @return array
     * @throws AppException
     */
    public function get(): array
    {
        $data = $this->getData();
        foreach ($data as $post) {
            if (!is_array($post)) {
                continue;
            }
            $this->collectStats($post);
        }
        $this->calculate();
        return $this->totals;
    }

    /**
     * Collect data
     * @param array $post
     */
    public function collectStats(array $post): void
    {
        $week = $post['date']['week'];
        $month = $post['date']['month'];
        $year = $post['date']['year'];
        $userId = $post['user']['id'];

        $this->totals[self::FIELD_LENGTH_PER_MONTH][$year][$month][] = $post['length'];

        if (!isset($this->totals[self::FIELD_USER_PER_MONTH][$year][$month][self::FIELD_USERS][$userId])) {
            $this->totals[self::FIELD_USER_PER_MONTH][$year][$month][self::FIELD_USERS][$userId] = 1;
        }
        else {
            $this->totals[self::FIELD_USER_PER_MONTH][$year][$month][self::FIELD_USERS][$userId]++;
        }

        // Fix a bit of an oddness about ISO 8601 dates
        if ($month === 1 && $week === 53) {
            $year--;
        }
        if (!isset($this->totals[self::FIELD_TOTAL_PER_WEEK][$year][$week])) {
            $this->totals[self::FIELD_TOTAL_PER_WEEK][$year][$week] = 1;
        }
        else {
            $this->totals[self::FIELD_TOTAL_PER_WEEK][$year][$week]++;
        }
    }

    /**
     * Do the calculations
     */
    protected function calculate(): void
    {
        foreach ($this->totals[self::FIELD_USER_PER_MONTH] as $yearKey => $year) {
            foreach ($year as $monthKey => $month) {
                unset($this->totals[self::FIELD_USER_PER_MONTH][$yearKey][$monthKey][self::FIELD_USERS]);
                $this->totals[self::FIELD_USER_PER_MONTH][$yearKey][$monthKey] = round(array_sum($month[self::FIELD_USERS])/count($month[self::FIELD_USERS]), 2);
            }
        }
        foreach ($this->totals[self::FIELD_LENGTH_PER_MONTH] as $yearKey => $year) {
            foreach ($year as $monthKey => $month) {
                unset($this->totals[self::FIELD_LENGTH_PER_MONTH][$yearKey][$monthKey]);
                $this->totals[self::FIELD_LENGTH_PER_MONTH][$yearKey][$monthKey] = round(array_sum($month)/count($month), 2);
                $this->totals[self::FIELD_LONGEST_POST_PER_MONTH][$yearKey][$monthKey] = max($month);
            }
        }
    }

    /**
     * @return array
     * @throws AppException
     */
    protected function getData()
    {
        return (new Posts())->getAll(new PostTransformer());
    }

}
