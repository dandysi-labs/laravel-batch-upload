# Laravel Batch Upload

A framework to help manage/automate/process data uploads (such as CSV), using your business logic. It has potential to be much more than just a databse update tool.

* __Scheduling__ - Define when you would like uploads to be processed
* __Validation__ - Add rules to ensure that data is valid before being processed and provide feedback on any failures
* __Configurable__ - Global or granular per processor
* __Headless__ - Create the frontend and approval process to suite your exact needs

## Install

```bash
composer require dandysi/laravel-batch-upload
```

## Config

```bash
php artisan vendor:publish --provider="Dandysi\Laravel\BatchUpload\BatchUploadServiceProvider"
```

Configure this package by changing the values in `config/batch-upload.php`.

## Getting Started

The first step is to create a processor (an engine for processing row data). It is a simple PHP class and can be created with the following maker command:

```bash
php artisan make:batch-upload-processor CreateCategoriesProcessor create_categories
```
Add the processor to the `config/batch-upload.php` config file.

```php
/**
 * Register processors here
 */
'processors' => [
    App\BatchUploads\CreateCategoriesProcessor::class
],
```

Define the columns/validation rules and implement code to handle the uploaded row data.

```php
class CreateCategoriesProcessor implements ProcessorInterface
{
    public function config(): ProcessorConfig 
    {
        return ProcessorConfig::create()
            ->column('code', 'Code', 'required')
            ->column('name', 'Name', 'required')
        ;
    }

    public function __invoke(array $row): void
    {
        $category = Cagegory::create([
            'code' => $row['code'],
            'name' => $row['name]
        ]);

        //more than just a simple data upload as you can add any other business logic here
    }

```

## Creating Batches

Ordinarily this would not be in one step, however the below outlines all the required stages.

```php
use Dandysi\Laravel\BatchUpload\Services\BatchService;

//Step 1 - Create
$service = app(BatchService::class);
$options = $service->options('create_categories', '/data/categories.csv');

$batch = $service->create($options);

//Step 2 - Approve
$batch->status = Batch::STATUS_APPROVE;
$batch->save();

//Step 3 - Dispatch (each row will be a seperate queued job)
$service->dispatch($batch);

```
If validation errors occur, they will be recorded against each row and the status of the batch/row will reflect this.

## Scheduled Batches

Schedule batches with an additional option and not performing step 3 above.

```php
$options = $service
    ->options('create_categories', '/data/categories.csv')
    ->schedule(now()->tommorow())
;
```

<a id="dispatch-command"></a>To ensure scheduled batches are dispatched you will need to add a schedule command in the console kernel:

```php
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('batch-upload:dispatch')-->everyTenMinutes();
    }
```
or create your own cron entry to execute the following:

```bash
php artisan batch-upload:dispatch
```

## User Batches

If your batches need to be identifiable by users, another option can be added during the creation step:

```php
$options = $service
    ->options('create_categories', '/data/categories.csv')
    ->user('user123')
;
```

## Console Commands

Create and dispatch a batch straight away:

```bash
php artisan batch-uploads:create create_categories /data/categories.csv --force-dispatch
```
> delay by minutes `--delay=60` or indentify with a user `--user=user123`. Delays will require the [schedule command/cron](#dispatch-command) step outlined above to be in place.

## License

Open-sourced software licensed under the [MIT license](LICENSE).