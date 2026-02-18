.PHONY: build dev clean

build:
	@bash build.sh

dev:
	@npm run dev --prefix resources

clean:
	@rm -rf builds/* assets/admin/
	@echo "Cleaned build outputs."
