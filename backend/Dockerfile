# Use the Python 3.11 slim base image
FROM python:3.11-slim

# Set the working directory in the container
WORKDIR /work_directory

# Update the package list and install necessary dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Install pycryptodome and requests packages
RUN pip install --no-cache-dir pycryptodome requests

# Run the script when the container starts
CMD ["python", "brute.py"]