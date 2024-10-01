<?php

namespace Taketool\Sysinfo\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\DebugUtility;

class UserService
{
    private array $allGroups = [];
    private int $subGroupLevel = 0;
    private array $users;
    protected ConnectionPool $connectionPool;

    public function __construct(
        ConnectionPool $connectionPool
    )
    {}

    /**
     * @throws Exception
     */
    public function buildGroupTree(): string
    {
        $result = '';
        $groups = $this->getGroups();
        $this->users = $this->getUsers();
        //DebugUtility::debug($this->users, '$this->users'); die();
        //DebugUtility::debug($groups, '$groups'); die();

        // find all groups
        foreach ($groups as $g) {
            $this->allGroups[$g['uid']] = $g;
            /*
            if ($g['subgroup'] != '') {  // @todo
                $parentGroups[$g['uid']] = $g;
            }
            */
        }
        //DebugUtility::debug($this->allGroups, 'allGroups');
        //DebugUtility::debug($parentGroups, '$parentGroups');
        foreach($this->allGroups as $uid => $pG) {
            $this->subGroupLevel = 0;
            $result .= '<ul>' . $this->getSubGroups($uid) . '</ul>';
        }
        return $result;
    }

    private function getSubGroups(int $gUid): string
    {
        $this->subGroupLevel += 1;
        //DebugUtility::debug($this->allGroups[$gUid]['subgroup'],$gUid.':'.$this->allGroups[$gUid]['title']);
        if ($this->allGroups[$gUid]['subgroup'] == '') {
            $this->subGroupLevel -= 1;
            return '<li>' . $gUid . ':<b>' . $this->allGroups[$gUid]['title'] . '</b><br>' . $this->usersFromGroups($gUid) . '</li>';
        }
        $res = '<li>' . $gUid . ':<b>' . $this->allGroups[$gUid]['title']  . '</b><br>'
            . $this->usersFromGroups($gUid)
            . '</li><ul>';
        if ($this->subGroupLevel <10) {
            foreach (explode(',', $this->allGroups[$gUid]['subgroup']) as $uid) {
                if ((int)$uid == $gUid) {
                    $res .= '<span class="text-primary">** self reference detected **</span>';
                } else {
                    $res .= $this->getSubGroups((int)$uid);
                }
            }
        } else {
            $res .= '<span class="text-primary">** maximum level reached **</span>';
        }

        return $res . '</ul>';
    }

    /**
     * @throws Exception
     */
    private function getUsers(): array
    {
        $q = $this->connectionPool->getQueryBuilderForTable('be_users');
        $res = $q
            ->select('uid', 'username', 'usergroup', 'realName', 'admin')
            ->from('be_users')
            ->where(
                $q->expr()->eq('deleted', 0)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $users = [];
        foreach ($res as $r) {
            $users[$r['uid']] = [
                'username' => $r['username'],
                'realName' => $r['realName'],
                'usergroup' => $r['usergroup'],
                'admin' => $r['admin'],
            ];
        }
        return $users;
    }

    /**
     * @throws Exception
     */
    private function getGroups(): array
    {
        $q = $this->connectionPool->getQueryBuilderForTable('be_groups');
        return $q
            ->select('uid', 'title', 'subgroup')
            ->from('be_groups')
            ->where(
                $q->expr()->eq('deleted', 0)
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    private function usersFromGroups($gUid): string
    {
        $users = [];
        foreach ($this->users as $u) {
            if (!empty($u['usergroup'])) {
                $userGroups = explode(',', $u['usergroup']);
                if (in_array($gUid, $userGroups) ) $users[] = ($u['admin'] == 1)
                    ? '<span class="text-primary">' . $u['username'] . '</span>'
                    : $u['username'] . ' (' . $u['realName'] . ')';
            }
        }
        asort($users);
        if ($users) return '<ul><li>' . implode('<br>', $users) . '</li></ul>';
        else return '';
    }
}