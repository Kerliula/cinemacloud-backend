# Start this image from this base node image
FROM node:24

# Set the working directory inside the container
# So all commands will run inside /app folder
WORKDIR /app

# Copy package.json and package-lock.json (if available)
# So that we can install dependencies inside the container
# When running "docker build", context directory is set and from there files are copied, not from local machine 
# Docker treats the build context as the root for any source paths in COPY or ADD commands
# COPY <src> (build context) <dest> (local container)
COPY package*.json ./

# Install application dependencies
RUN npm install 

# Copy the rest of the application code to the working directory
# From the build context to the local container (/app folder inside the container) 
COPY . .

# Make the entrypoint script executable and set it as the container's entrypoint
# This script will run every time the container starts
# It ensures that database migrations are applied before starting the application
RUN chmod +x /app/docker-prisma-entrypoint.sh 
ENTRYPOINT ["/app/docker-prisma-entrypoint.sh"]

# Expose the port that the application will run on
EXPOSE 3000

# Default command to run the container
# Start the application in production mode
# Note: Only one CMD can be used in a Dockerfile. 
# If multiple CMD instructions are specified, only the last one will take effect. 
# This command is overridden in docker-compose.yml for development mode.
CMD ["npm", "start"]