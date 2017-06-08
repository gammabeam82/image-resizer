<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use claviska\SimpleImage;

class ResizeCommand extends Command
{
	const DESTINATION_DIR = "resized";
	const MAXWIDTH = 600;
	const MAXHEIGHT = 800;
	const ALLOWED_TYPES = [
		'image/jpeg', 'image/png'
	];

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('resize')
			->setDescription('This command resizes images in specified directory.')
			->addArgument(
				'directory',
				InputArgument::REQUIRED,
				'Relative path to the images directory.'
			)
			->addOption(
				'maxWidth',
				null,
				InputOption::VALUE_OPTIONAL,
				'New max width of the image.',
				self::MAXWIDTH
			)
			->addOption(
				'maxHeight',
				null,
				InputOption::VALUE_OPTIONAL,
				'New max height of the image.',
				self::MAXHEIGHT
			)
			->addOption(
				'destinationDir',
				null,
				InputOption::VALUE_OPTIONAL,
				'Directory name for saving processed images.',
				self::DESTINATION_DIR
			);
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$directory = $input->getArgument('directory');

		$io = new SymfonyStyle($input, $output);

		if (false === is_dir($directory)) {
			$io->error(sprintf("Invalid path: %s", $directory));
			return;
		}

		$io->writeln(['Fetching data...']);

		$path = sprintf("%s/%s", $directory, $input->getOption('destinationDir'));

		$originalFiles = array_filter(
			glob(sprintf("%s/*.*", $directory)),
			function ($item) {
				return in_array(mime_content_type($item), self::ALLOWED_TYPES);
			}
		);

		$filesCount = count($originalFiles);

		$io->writeln([
			sprintf("Found: %s", count($originalFiles)),
			''
		]);

		if (0 === $filesCount) {
			return;
		}

		if (false === file_exists($path)) {
			mkdir($path);
		}

		$io->writeln(['Processing...']);
		$io->progressStart($filesCount);

		foreach ($originalFiles as $file) {
			$image = new SimpleImage();

			$filename = sprintf("%s/%s", $path, basename($file));

			try {
				$image
					->fromFile($file)
					->bestFit($input->getOption('maxWidth'), $input->getOption('maxHeight'))
					->toFile($filename);
			} catch (\Exception $e) {
				$io->error(sprintf("File processing error %s: %s", basename($file), $e->getMessage()));
				return;
			}

			$io->progressAdvance(1);

			unset($image);
		}

		$io->progressFinish();
		$io->success('Done!');
	}
}