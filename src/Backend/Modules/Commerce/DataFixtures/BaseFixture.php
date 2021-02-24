<?php

namespace Backend\Modules\Commerce\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use PhpOffice\PhpSpreadsheet\Reader\Csv as CsvReader;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class BaseFixture extends Fixture
{
    protected array $tableNames = [];
    protected array $uploadFolders = [];
    protected MessageBus $commandBus;
    protected CsvReader $csvReader;

    public function __construct(MessageBus $commandBus)
    {
        $this->commandBus = $commandBus;
        $this->csvReader = new CsvReader();
    }

    protected function cleanup(ObjectManager $manager): void
    {
        // Cleanup tables
        $connection = $manager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($this->tableNames as $name) {
            $connection->executeUpdate($platform->getTruncateTableSQL($name, false));
        }
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');

        // Cleanup uploads
        $fs = new Filesystem();
        $fs->remove($this->uploadFolders);
    }

    public function fakeUploadImage(string $sourcePath, string $targetDir = null): UploadedFile
    {
        $fs = new Filesystem();
        $targetDir ??= sys_get_temp_dir();
        $targetPath = $targetDir.'/'.pathinfo($sourcePath, PATHINFO_FILENAME).uniqid().'.'.pathinfo($sourcePath, PATHINFO_EXTENSION);
        $fs->copy($sourcePath, $targetPath, true);

        return new UploadedFile($targetPath, basename($targetPath), null, null, null, true);
    }

    public function readCsv(string $csvPath): array
    {
        $csv = $this->csvReader->load($csvPath);
        $rows = $csv->getActiveSheet()->toArray();
        $header = array_shift($rows);

        $csv = [];
        foreach ($rows as $row) {
            $csv[] = array_combine($header, $row);
        }

        return $csv;
    }
}
