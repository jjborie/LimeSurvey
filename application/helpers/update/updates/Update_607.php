<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

/**
 * Fix organizer link : icon and survey activated
 * @package LimeSurvey\Helpers\Update
 */
class Update_607 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws \CException
     */
    public function up()
    {
        $surveyThemes = $this->db->createCommand()
            ->select('*')
            ->from('{{templates}}')
            ->queryAll();
        if (!empty($surveyThemes)) {
            foreach ($surveyThemes as $surveyTheme) {
                $surveyThemeName = $surveyTheme['name'];
                setTransactionBookmark();
                try {
                    if (!$this->isStandardTemplate($surveyThemeName)) {
                        $rowsDeleted = $this->db->createCommand()->delete(
                            '{{templates}}',
                            'name=:name',
                            [':name' => $surveyThemeName]
                        );
                        if ($rowsDeleted >= 1) {
                            $this->db->createCommand()->delete(
                                '{{template_configuration}}',
                                'template_name=:templateName',
                                [':templateName' => $surveyThemeName]
                            );
                        }
                    }
                } catch (Exception $e) {
                    rollBackToTransactionBookmark();
                }
            }
        }
    }

    /**
     * Check if a survey theme is a core theme
     * @param $surveyThemeName
     * @return bool
     */
    private function isStandardTemplate($surveyThemeName): bool
    {
        static $templatesInStandardDir = null;
        if (empty($templatesInStandardDir)) {
            $templateList = [];
            $standardTemplateRootDir = App()->getConfig("standardthemerootdir");
            if ($standardTemplateRootDir && $dirHandle = opendir($standardTemplateRootDir)) {
                while (false !== ($fileName = readdir($dirHandle))) {
                    if (!is_file("$standardTemplateRootDir/$fileName") && $fileName !== "." && $fileName !== ".." && $fileName !== ".svn" && (file_exists(
                            "{$standardTemplateRootDir}/{$fileName}/config.xml"
                        ))) {
                        $templateList[$fileName] = $standardTemplateRootDir . DIRECTORY_SEPARATOR . $fileName;
                    }
                }
                closedir($dirHandle);
            }
            $templatesInStandardDir = array_keys($templateList);
        }
        return in_array($surveyThemeName, $templatesInStandardDir, false);
    }
}
