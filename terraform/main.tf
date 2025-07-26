provider "aws" {
  region = "eu-west-1" # or your preferred region
}

resource "aws_instance" "app" {
  ami           = "ami-0c55b159cbfafe1f0" # Ubuntu 22.04 (update if needed)
  instance_type = "t2.micro"

  user_data = file("setup.sh")

  key_name = "lwa-key" # Replace with your key

  tags = {
    Name = "LaravelApp"
  }

  vpc_security_group_ids = [aws_security_group.laravel_sg.id]
}

resource "aws_security_group" "laravel_sg" {
  name        = "laravel-sg"
  description = "Allow HTTP and SSH"
  ingress = [
    {
      from_port   = 22
      to_port     = 22
      protocol    = "tcp"
      cidr_blocks = ["0.0.0.0/0"]
    },
    {
      from_port   = 80
      to_port     = 80
      protocol    = "tcp"
      cidr_blocks = ["0.0.0.0/0"]
    }
  ]
  egress = [
    {
      from_port   = 0
      to_port     = 0
      protocol    = "-1"
      cidr_blocks = ["0.0.0.0/0"]
    }
  ]
}

output "ec2_public_ip" {
  value = aws_instance.app.public_ip
}
