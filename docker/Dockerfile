FROM bref/php-82-fpm:2

# Copy the source code in the image
COPY . /var/task

# Configure the handler file (the entrypoint that receives all HTTP requests)
CMD ["public/index.php"]
