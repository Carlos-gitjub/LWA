provider "aws" {
  region = "eu-west-1" # or your preferred region
}

resource "aws_instance" "app" {
  ami           = "ami-0043735dd4439caed" # Ubuntu 22.04 (update if needed)
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
      description      = "SSH"
      from_port        = 22
      to_port          = 22
      protocol         = "tcp"
      cidr_blocks      = ["0.0.0.0/0"]
      ipv6_cidr_blocks = []
      prefix_list_ids  = []
      security_groups  = []
      self             = false
    },
    {
      description      = "HTTP"
      from_port        = 80
      to_port          = 80
      protocol         = "tcp"
      cidr_blocks      = ["0.0.0.0/0"]
      ipv6_cidr_blocks = []
      prefix_list_ids  = []
      security_groups  = []
      self             = false
    }
  ]

  egress = [
    {
      description      = "All outbound"
      from_port        = 0
      to_port          = 0
      protocol         = "-1"
      cidr_blocks      = ["0.0.0.0/0"]
      ipv6_cidr_blocks = []
      prefix_list_ids  = []
      security_groups  = []
      self             = false
    }
  ]
}


output "ec2_public_ip" {
  value = aws_instance.app.public_ip
}
