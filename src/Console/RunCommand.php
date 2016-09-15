<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 12.09.2016
 * Time: 9:12
 */

namespace SmkSoftware\ScrapeIt\Console;


use SmkSoftware\ScrapeIt\ScrapeClient;
use SmkSoftware\ScrapeIt\Settings;
use SmkSoftware\ScrapeIt\Utils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    const CRAWLER = 'crawler';
    const CONCURRENCY = 'concurrency';
    const PATH = 'path';
    const SETTINGS = 'settings';
    const OUTPUT_FORMAT = 'output-format';
    const INPUT_FORMAT = 'input-format';
    const OUTPUT = 'output';
    const INPUT = 'input';

    /*
        public function __construct($name)
        {
            parent::__construct($name);
        }
    */
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Run crawler for execution')
            ->setHelp("Run crawler for execution")
            ->addArgument(self::CRAWLER, InputArgument::REQUIRED, 'Crawler name to start')
            ->addOption(self::CONCURRENCY, 'c', InputOption::VALUE_OPTIONAL, 'Overrides concurrency level')
            ->addOption(self::PATH, 'p', InputOption::VALUE_OPTIONAL, 'Path where to crawlers', './')
            ->addOption(self::SETTINGS, 's', InputOption::VALUE_OPTIONAL, 'Settings file name', './Settings.php')
            ->addOption(self::OUTPUT_FORMAT, 'f', InputOption::VALUE_OPTIONAL, 'Output format (csv, json, ...)')
            ->addOption(self::OUTPUT, 'o', InputOption::VALUE_OPTIONAL, 'Output file name or storage name')
            ->addOption(self::INPUT_FORMAT, 'if', InputOption::VALUE_OPTIONAL, 'Input format (csv, json, ...)')
            ->addOption(self::INPUT, 'i', InputOption::VALUE_OPTIONAL, 'Input file name or source name');
    }

    protected function includeClass($scraper, $path)
    {
        $scraperFileName = $path . '/' . $scraper . '.php';
        if (!file_exists($scraperFileName)) {
            return [null, "Can't find file '" . $scraperFileName . "'"];
        }


        require_once $scraperFileName;

        $instance = Utils::createClass($scraper, [], ScrapeClient::class);
        if ($instance === Utils::CREATECLASS_ERROR_NOT_FOUND) {
            return [null, "Can't find class $scraper in file $scraperFileName"];
        } elseif ($instance === Utils::CREATECLASS_ERROR_NOT_INSTANCE_OF) {
            return [null, "Crawler $scraper is not instance of ScrapeClient!"];
        }

        return [$instance, null];
    }

    private function applySettings(ScrapeClient $crawler, InputInterface $input)
    {
        $settingsFileName = $input->getOption(self::SETTINGS);
        $settings = new Settings();
        if (!$settings->load($settingsFileName)) {
            return "Can't load settings from '$settingsFileName'";
        }

        // TODO: Warn if input specified inputFormat is ignored!
        $warn = '';

        // TODO: validate input
        $settings->setSetting(ScrapeClient::SET_CONCURRENCY, $input->getOption(self::CONCURRENCY));
        $settings->setSetting(ScrapeClient::SET_OUTPUT, $input->getOption(self::OUTPUT));
        $settings->setSetting(ScrapeClient::SET_OUTPUT_FORMAT, $input->getOption(self::OUTPUT_FORMAT));
        $settings->setSetting(ScrapeClient::SET_INPUT, $input->getOption(self::INPUT));
        $settings->setSetting(ScrapeClient::SET_INPUT_FORMAT, $input->getOption(self::INPUT_FORMAT));
        $crawler->settings->setSettings($settings);

        return $warn;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $crawlerName = $input->getArgument(self::CRAWLER);
        $path = $input->getOption(self::PATH);

        /** @var ScrapeClient $crawler */
        list($crawler, $error) = $this->includeClass($crawlerName, $path);
        if (!$crawler) {
            $output->writeln($error);
            return;
        }

        $warn = $this->applySettings($crawler, $input);
        if ($warn) {
            $output->writeln($warn, OutputInterface::VERBOSITY_VERBOSE);
        }

        $output->writeln("Crawler $crawlerName started!");
        $crawler->run();
        $output->writeln(["Crawler $crawlerName finished!"]);
    }
}