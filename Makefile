.PHONY: build dev clean

build:
	@rm -rf builds/* assets/admin/
	@echo "Cleaned build outputs."
	@bash build.sh

dev:
	@npm run dev --prefix resources

clean:
	@rm -rf builds/* assets/admin/
	@echo "Cleaned build outputs."
