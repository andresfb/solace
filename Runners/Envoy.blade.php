@include('vendor/autoload.php')

@setup
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $stgWeb = env('STG_USER_ONE') . '@' . env('STG_WEB_HOST_ONE');

    $stgWorkers = [
        env('STG_USER_ONE') . '@' . env('STG_HORIZON_DELL_HOST');
        env('STG_USER_ONE') . '@' . env('STG_HORIZON_TIGER_HOST');
        env('STG_USER_TWO') . '@' . env('STG_RUNNER_TIGER_HOST_UNOX');
        env('STG_USER_TWO') . '@' . env('STG_RUNNER_TIGER_HOST_DUOX');
        env('STG_USER_TWO') . '@' . env('STG_RUNNER_DELL_HOST_TREX');
        env('STG_USER_TWO') . '@' . env('STG_RUNNER_DELL_HOST_QUAX');
        {{-- TODO: add the Encoders info --}}
    ]

    $stgDeployWebPath = env('DEPLOY_LOCATION_WEB')
    $stgDeployWrkPath = env('DEPLOY_LOCATION_WORKERS')
@endsetup

@servers([
    'local' => ['127.0.0.1'],
    'stg-web' => [$stgWeb],
    'stg-workers' => $stgWorkers,
])

@story('deploy-stg')
    upload-stg
    assets-stg
    worker-stg
@endstory

@story('composer-stg')
    composer-web-stg
    composer-wrk-stg
@endstory

@task('upload-stg', ['on' => 'local'])
    echo "Uploading to Web"
    rsync -avh --delete --exclude-from deploy-exclude.txt . {{ $stgWeb }}:{{ $stgDeployWebPath }}
    echo "Uploading to workers"
    @foreach($stgWorkers as $worker)
        rsync -avh --delete --exclude-from deploy-exclude.txt . {{ $worker }}:{{ $stgDeployWrkPath }}
    @endforeach
@endtask

{{--@task('assets-stg', ['on' => 'stg-web'])--}}
{{--    source ~/.nvm/nvm.sh--}}
{{--    cd {{ env('DEPLOY_LOCATION') }}--}}
{{--    npm install && npm run build--}}
{{--    php artisan view:clear--}}
{{--    php artisan config:clear--}}
{{--    php artisan route:clear--}}
{{--    php artisan route:cache--}}
{{--    php artisan cache:clear--}}
{{--@endtask--}}

@task('restart-worker-stg', ['on' => 'stg-workers'])
    cd {{ $stgDeployWrkPath }}
    php artisan config:clear
    sudo /usr/bin/supervisorctl restart solace:*
@endtask

@task('composer-web-stg', ['on' => ['stg-web'] ])
    cd {{ $stgDeployWebPath }}
    composer install
@endtask

@task('composer-wrk-stg', ['on' => ['stg-workers'] ])
    cd {{ $stgDeployWrkPath }}
    composer install
@endtask
